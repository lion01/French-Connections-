<?php
/**
 * @version: $Id: cookie.php 942 2011-03-06 23:00:23Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-03-07 00:00:23 +0100 (Mon, 07 Mar 2011) $
 * $Revision: 942 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/env/cookie.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * Cookie handler
 * @author Radek Suski
 * @version 1.0
 * @created 10-Feb-2010 09:25:42
 */
abstract class SPCookie
{
	const prefix = 'SPro_';

	/**
	 * @param string $name - The name of the cookie.
	 * @param string $value - The value of the cookie
	 * @param int $expire - The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch
	 * @param bool $httponly - When true the cookie will be made accessible only through the HTTP protocol.
	 * @param bool $secure - Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client
	 * @param string $path - The path on the server in which the cookie will be available on
	 * @param string $domain - The domain that the cookie is available
	 * @return bool
	 */
	public static function set( $name, $value, $expire = 0, $httponly = false, $secure = false, $path = '/', $domain = null )
	{
		$name = self::prefix.$name;
		$expire = ( $expire == 0 ) ? $expire : time() + $expire;
		return setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly ) && SPRequest::string( $name, null, false, 'cookie' );
	}

	/**
	 * Delete cookie
	 * @param $name - The name of the cookie.
	 * @return bool
	 */
	public static function delete( $name )
	{
		$name = self::prefix.$name;
		return setcookie( $name, '', ( time() - 36000 ) );
	}

	/**
	 * convert hours to minutes
	 * @param int $time number of minutes
	 * @return int
	 */
	public static function minutes( $time )
	{
		return $time * 60;
	}

	/**
	 * convert hours to seconds
	 * @param int $time number of hours
	 * @return int
	 */
	public static function hours( $time )
	{
		return self::minutes( $time ) * 60;
	}

	/**
	 * convert days to seconds
	 * @param int $time number of days
	 * @return int
	 */
	public static function days( $time )
	{
		return self::hours( $time ) * 24;
	}
}
?>