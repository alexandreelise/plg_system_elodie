<?php
declare(strict_types=1);
/**
 * Alex Api Serializer
 *
 * @version       0.1.0
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
use Joomla\CMS\Filter\OutputFilter;
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
	 * @var array
	 * @since version
	 */
	private $sparseFieldsetQueryString;
	/**
	 * @var array
	 * @since version
	 */
	private $filterQueryString;

	/**
	 * Constructor
	 *
	 * @param   string  $type
	 */
	public function __construct(
		string $type
	)
	{
		//NOTE: not clean to put input there but I wanted this class to have the same
		// signature than the parent class so I didn't put extra parameters that would be
		// convenient to inject the input rather than doing it in this constructor
		$input                           = Factory::getApplication()->input;
		$this->sparseFieldsetQueryString = $input->get('fields', [], 'ARRAY');
		$this->filterQueryString         = $input->get('filter', [], 'ARRAY');
		$this->type                      = $type;
	}


	/**
	 * @inheritDoc
	 */
	public function getAttributes($model, array $fields = null)
	{
		$chosenFields                       = array_values(array_filter(explode(',', ($this->sparseFieldsetQueryString[$this->type]) ?? '')));
		$baseAttributesWithChosenFieldsOnly = $this->baseTraitGetAttributes($model, $chosenFields);
		$filters                            = $this->filterQueryString;

		return CommonHelper::filterAttributes(
			$baseAttributesWithChosenFieldsOnly,
			$filters
		);
	}

}
