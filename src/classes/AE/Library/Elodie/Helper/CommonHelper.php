<?php
declare(strict_types=1);
/**
 * CommonHelper
 *
 * @version       0.1.0
 * @package       CommonHelper
 * @author        Alexandre ELISÉ <contact@alexapi.cloud>
 * @copyright (c) 2009-2021 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * @link          https://alexapi.cloud
 */

namespace AE\Library\Elodie\Helper;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Uri\Uri;
use function array_filter;
use function is_string;
use function levenshtein;
use function trim;
use const ARRAY_FILTER_USE_BOTH;

defined('_JEXEC') or die;

/**
 * @package     AE\Library\Elodie\Helper
 *
 * @since       version
 */
abstract class CommonHelper
{
	/**
	 * Extract webservice resource type from uri
	 */
	public static function getWebserviceResourceType(?Uri $uri = null)
	{
		$actualUri = $uri ?? Uri::getInstance();

		return trim(mb_strrchr($actualUri->getPath(), '/'), '/');
	}

	/**
	 * Filter out key/value of assoc array using lightweight fuzzy matching
	 *
	 * @param   array  $attributes
	 * @param   array  $query
	 *
	 * @return array
	 *
	 * @since 0.1.0
	 */
	public static function filterAttributes(array $attributes, array $filters = []): array
	{
		// read all attibutes key/value pair k => v
		if (empty($filters))
		{
			return $attributes;
		}

		return array_filter($attributes, function ($v, $k) use ($filters) {
			// try to do fuzzy matching using levenstein distance algorithm
			return ((is_string($v)
					&& is_string($filters[$k])
					&& levenshtein(OutputFilter::cleanText($filters[$k]), OutputFilter::cleanText($v), 1, 2, 4) < 7
				)
				|| ($filters[$k] === $v));
		}, ARRAY_FILTER_USE_BOTH);
	}
}
