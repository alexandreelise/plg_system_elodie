<?php
declare(strict_types=1);
/**
 * Alex Api Serializer
 *
 * @version       1.0.0
 * @package       AlexApiSerializer
 * @author        Alexandre ELISÉ <contact@alexapi.cloud>
 * @copyright (c) 2009-2021 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * @link          https://alexapi.cloud
 */

namespace AE\Library\Elodie\Serializer;

use AE\Library\Elodie\Behaviour\JoomlaSerializerTrait;
use AE\Library\Elodie\Helper\CommonHelper;
use Joomla\CMS\Factory;
use Tobscure\JsonApi\AbstractSerializer;

defined('_JEXEC') or die;

/**
 * @note        Not clean to extend JoomlaSerializer directly,
 * rather using DI Container to do this but since JoomlaSerializer is not
 * registered in the DI Container, I can't overload it cleanly that way.
 * For now I copied the methods from JoomlaSerializer manually in a trait to use it more easily,
 * then, I load my version of the JoomlaSerializer early using a system plugin
 *
 * @package     AE\Library\Elodie\Serializer
 *
 * @since       0.1.0
 */
class AlexApiSerializer extends AbstractSerializer
{
	use JoomlaSerializerTrait
	{
		JoomlaSerializerTrait::getAttributes as baseTraitGetAttributes;
		JoomlaSerializerTrait::getRelationship as baseTraitGetRelationship;
	}

	/**
	 * Constructor
	 *
	 * @param   string  $type
	 */
	public function __construct(string $type)
	{
		$this->type                      = $type;
	}

	/**
	 * Custom version of getAttributes which allows us
	 * to take query string fields into account when retrieving results
	 * of jsonapi document
	 *
	 * @param   mixed       $model
	 * @param   array|null  $fields
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function getAttributes($model, array $fields = null)
	{
		//NOTE: Not clean but works for now
		//Fields chosen by user when typing the url of the webservice call
	    //eg: https://example.org/api/index.php/v1/users?fields[users]=id,name
		$sparseFieldsetQueryString = Factory::getApplication()->input->get('fields', [], 'ARRAY');

		// extract the fields as array rather than comma separated values
		$chosenFields                       = array_values(array_filter(explode(',', ($sparseFieldsetQueryString[$this->type]) ?? '')));
		return $this->baseTraitGetAttributes($model, empty($chosenFields) ? $fields : $chosenFields);
	}

}
