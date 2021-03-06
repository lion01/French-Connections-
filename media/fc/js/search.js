
jQuery(document).ready(function(){

  // Maphilight is used to highlight the area maps on the map search graphic
  jQuery('.map').maphilight();

  // Works on the tabs on the search results page. Needs to be made more generic
  jQuery('a[data-toggle="tab"]').on('shown', function (e) {

    // Store the selected tab #ref in local storage, IE8+
    localStorage['selectedTab']=jQuery(e.target).attr('href');

    // Get the selected tab from the local storage
    var selectedTab = localStorage['selectedTab'];

    // If the selected tab is the map tag then grab the markers
    if (selectedTab == '#mapsearch') {

      // Get jiggy with the Google Maps, innit!
      if (!document.map) {
        initmap();
      }

      // Get the search parameters, quicker to get the form and then extract the inputs?
      // Let's get all the form input elements - more performant to do it in one go rather than getting each via a separate DOM lookup
      path ='';
      inputs = jQuery('#property-search').find(':input').each(function() {

        id = jQuery(this).attr('id');
        value = jQuery(this).attr('value');
        if (value && id) {
          if (id == 's_kwds') {
            value = stripVowelAccent(value);
            path = path + value;
          } else if (id == 'filter') {
            path = path + '/' + value;
          } else if (id == 'sort_by') {
            path = path + '/' + value;
          } else if (id == 'min_price') {
            path = path + '/' + value;
          } else if (id == 'max_price') {
            path = path + '/' + value;
          } else {
            path = path+'/'+id+'_'+value;
          }
        }
      })



      // Do an ajax call to get a list of towns...
      jQuery.getJSON("/index.php?option=com_fcsearch&task=mapsearch.markers&format=json",{
        s_kwds:path
      },
      function(data){

        // Get the map instance
        map = document.map;

        markers = {};

        // Loop over all data (properties) and create a new marker
        for (var i = 0; i < data.length; i++) {

          // The lat long of the propert, units will appear stacked on top...
          var myLatlng = new google.maps.LatLng(data[i].latitude,data[i].longitude);

          // Create the marker instance
          marker = new google.maps.Marker({
            position: myLatlng,
            map: map
          });

          marker.setTitle((i + 1).toString());
          content = '<h4>'+data[i].property_title+'</h5>'+'<a href="'+data[i].link+'"><img src="'+data[i].thumbnail+'"/></a><p>'+data[i].pricestring+'</p>';
          attachContent(marker, content);

          markers[i] = marker;

          //  Create a new viewpoint bound, so we can centre the map based on the markers
          var bounds = new google.maps.LatLngBounds();

          //  Go through each...
          jQuery.each(markers, function (index, marker) {
            bounds.extend(marker.position);
          });

          //  Fit these bounds to the map
          map.fitBounds(bounds);
        }
      });
    }
  });




  // Get the selected tab, if any and set the tab accordingly...
  var selectedTab = localStorage['selectedTab'];

  if (selectedTab == '#mapsearch') {
    jQuery('.nav li a[href="'+selectedTab+'"]').tab('show');
  }




  jQuery('#property-search-button').click(function(event) {


    // val is the 'active' suggestion populated by typeahead
    // e.g. the option chosen should be the last active one
    var val = jQuery(".typeahead.dropdown-menu").find('.active').attr('data-value');

    // The value contained in the typeahead field
    var chosen = jQuery(".typeahead").attr('value');

    // Double check that the typeahead has any elements, if not then it means it's already populated, e.g. when you land on a search results page
    var count = jQuery(".typeahead.dropdown-menu li").length;

    // If chosen not empty and count not zero, chances are we have a auto suggestion choice
    if (chosen !== '' && count !== 0) {
      if ( val !== chosen) {
        jQuery('#myModal').modal();
        return false;
      }
    } else if (chosen == '') { // otherwise, just check that the chosen field isn't empty...check the q var on the server side
      jQuery('#myModal').modal();
      return false;
    }

    // Form checks out, looks like the user chose something from the suggestions
    // Strip the string to make it like classifications table alias
    var query = stripVowelAccent(chosen);

    // The path of the search, e.g. /search or /fr/search
    var path = '/search';

    // Let's get all the form input elements - more performant to do it in one go rather than getting each via a separate DOM lookup
    inputs = jQuery('#property-search').find(':input').each(function() {

      id = jQuery(this).attr('id');
      value = jQuery(this).attr('value');
      if (value && id) {
        if (id == 's_kwds') {
          value = stripVowelAccent(value);
          path = path + '/' + value;
        } else if (id == 'filter') {
          path = path + '/' + value;
        } else if (id == 'sort_by') {
          path = path + '/' + value;
        } else if (id == 'min_price') {
          path = path + '/' + value;
        } else if (id == 'max_price') {
          path = path + '/' + value;
        } else {
          path = path+'/'+id+'_'+value;
        }
      }
    });

    // Amend the path that the form is submitted to
    jQuery('form#property-search').attr('action', path);

    // Submit the form
    jQuery('form#property-search').submit();

    return false;

  })

  // Bind the typeahead business
  jQuery(".typeahead").typeahead({

    source: function (query, process) {
      jQuery.get( '/index.php?option=com_fcsearch&task=suggestions.display&format=json&tmpl=component',
      {
        q: query,
        items: 10
      },
      function (data) {
        process(data);
      }
      )
    }
  })

  jQuery(".show").click(function(event){
    event.preventDefault();
    jQuery(this).prev().prev().toggleClass('show');
  })

}) // End of on DOM ready

function initmap() {

  jQuery('#map_canvas').css('width','100%');
  jQuery('#map_canvas').css('height','500px');

  var myLatLng = new google.maps.LatLng(46.8,2.8);
  var myOptions = {
    center: myLatLng,
    zoom: 6,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    disableDefaultUI: true,
    zoomControl:true
  }
  var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
  document.map = map;
}

// The five markers show a secret message when clicked
// but that message is not within the marker's instance data
function attachContent(marker, num) {
  var infowindow = new google.maps.InfoWindow({
    content: num
  });

  google.maps.event.addListener(marker, 'click', function() {
    infowindow.open(marker.get('map'), marker);
  });
}


// Function removes and replaces all French accented characters with non accented characters
// as well as removing spurious characters and replacing spaces with dashes...innit!
function stripVowelAccent(str) {

  var s=str;
  var rExps=[
  /[\xC0-\xC2]/g,
  /[\xE0-\xE2]/g,
  /[\xC8-\xCA]/g,
  /[\xE8-\xEB]/g,
  /[\xCC-\xCE]/g,
  /[\xEC-\xEE]/g,
  /[\xD2-\xD4]/g,
  /[\xF2-\xF4]/g,
  /[\xD9-\xDB]/g,
  /[\xF9-\xFB]/g
  ];

  var repChar=['A','a','E','e','I','i','O','o','U','u'];

  for(var i=0; i<rExps.length; i++) {
    s=s.replace(rExps[i],repChar[i]);
  }

  // Replace braces with spaces
  s=s.replace(/[()]/g,' ');

  // Replace apostrophe with space
  s=s.replace(/[\']/g,' ');

  if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
      return this.replace(/^\s+|\s+$/g, '');
    }
  }

  // Trim any trailing space
  s=s.trim();

  // Remove any duplicate whitespace, and ensure all characters are alphanumeric
  s=s.replace(/(\s|[^A-Za-z0-9\-])+/g,'-');



  s=s.toLowerCase();

  return s;
}