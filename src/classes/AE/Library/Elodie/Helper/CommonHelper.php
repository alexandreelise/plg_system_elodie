<?php
declare(strict_types=1);
/**
 * CommonHelper
 *
 * @version       1.0.0
 * @package       CommonHelper
 * @author        Alexandre ELISÉ <contact@alexapi.cloud>
 * @copyright (c) 2009-2021 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * @link          https://alexapi.cloud
 */

namespace AE\Library\Elodie\Helper;

use Joomla\CMS\Uri\Uri;
use const FILTER_VALIDATE_INT;

defined('_JEXEC') or die;

/**
 * @package     AE\Library\Elodie\Helper
 *
 * @since       1.0.0
 */
abstract class CommonHelper
{
	/**
	 * Extract webservice resource type from uri
	 */
	public static function getWebserviceResourceType(?Uri $uri = null): string
	{
		$actualUri = $uri ?? Uri::getInstance();

		$result = explode('/', $actualUri->getPath());

		$last = array_pop($result);

		// if id at the end pop it and return what is before it
		if (is_int(filter_var($last, FILTER_VALIDATE_INT)))
		{
			return array_pop($result);
		}

		//if no id just return last segment of uri path
		return $last;
	}
}
