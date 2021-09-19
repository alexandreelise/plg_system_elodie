<?php
declare(strict_types=1);
/**
 * JoomlaSerializerTrait
 *
 * @version       1.0.0
 * @package       JoomlaSerializerTrait
 * @author        Alexandre ELISÉ <contact@alexapi.cloud>
 * @copyright (c) 2009-2021 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * @link          https://alexapi.cloud
 */

namespace AE\Library\Elodie\Behaviour;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Serializer\Events\OnGetApiAttributes;
use Joomla\CMS\Serializer\Events\OnGetApiRelation;
use Tobscure\JsonApi\Relationship;
use function array_flip;
use function array_intersect_key;
use function array_merge;
use function sprintf;

defined('_JEXEC') or die;

/**
 *
 * @note        Code from the core copied into this trait
 * to make it easier to use in this library
 * @package     AE\Library\Elodie\Behaviour
 *
 * @since       1.0.0
 */
trait JoomlaSerializerTrait
{
	/**
	 * Get the attributes array.
	 *
	 * @param   array|\stdClass|CMSObject  $post    The data container
	 * @param   array|null                 $fields  The requested fields to be rendered
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getAttributes($post, array $fields = null)
	{
		if (!($post instanceof \stdClass) && !(\is_array($post)) && !($post instanceof CMSObject))
		{
			$message = sprintf(
				'Invalid argument for %s. Expected array or %s. Got %s',
				static::class,
				CMSObject::class,
				\gettype($post)
			);

			throw new \InvalidArgumentException($message);
		}

		// The response from a standard ListModel query
		if ($post instanceof \stdClass)
		{
			$post = (array) $post;
		}

		// The response from a standard AdminModel query also works for Table which extends CMSObject
		if ($post instanceof CMSObject)
		{
			$post = $post->getProperties();
		}

		$event = new OnGetApiAttributes('onGetApiAttributes', ['attributes' => $post, 'context' => $this->type]);

		/** @var OnGetApiAttributes $eventResult */
		$eventResult  = Factory::getApplication()->getDispatcher()->dispatch('onGetApiAttributes', $event);
		$combinedData = array_merge($post, $eventResult->getAttributes());

		return \is_array($fields) ? array_intersect_key($combinedData, array_flip($fields)) : $combinedData;
	}

	/**
	 * Get a relationship.
	 *
	 * @param   mixed   $model  The model of the entity being rendered
	 * @param   string  $name   The name of the relationship to return
	 *
	 * @return \Tobscure\JsonApi\Relationship|void
	 *
	 * @since   4.0.0
	 */
	public function getRelationship($model, $name)
	{
		$result = parent::getRelationship($model, $name);

		// If we found a result in the content type serializer return now. Else trigger plugins.
		if ($result instanceof Relationship)
		{
			return $result;
		}

		$eventData = ['model' => $model, 'field' => $name, 'context' => $this->type];
		$event     = new OnGetApiRelation('onGetApiRelation', $eventData);

		/** @var OnGetApiRelation $eventResult */
		$eventResult = Factory::getApplication()->getDispatcher()->dispatch('onGetApiRelation', $event);

		$relationship = $eventResult->getRelationship();

		if ($relationship instanceof Relationship)
		{
			return $relationship;
		}
	}
}
