<?php
/**
 * System - Elodie
 *
 * @package    Elodie
 *
 * @author     Alexandre ELISÉ <contact@alexapi.cloud>
 * @copyright  Copyright(c) 2009 - 2021 Alexandre ELISÉ. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://alexapi.cloud
 */

defined('_JEXEC') or die;

use AE\Library\Elodie\Helper\CommonHelper;
use AE\Library\Elodie\Serializer\AlexApiSerializer;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Uri\UriInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Resource;

/**
 * Elodie plugin.
 *
 * @package   Elodie
 * @since     0.1.0
 */
class PlgSystemElodie extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object
	 *
	 * @var    \Joomla\CMS\Application\CMSApplicationInterface|null $app
	 * @since  0.1.0
	 */
	protected $app;
	
	/**
	 * Database object
	 *
	 * @var    \Joomla\Database\DatabaseDriver|null $db
	 * @since  0.1.0
	 */
	protected $db;
	
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean $autoloadLanguage
	 * @since  0.1.0
	 */
	protected $autoloadLanguage = true;
	
	/**
	 *
	 * @return string[]
	 *
	 * @since version
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterDispatch' => 'onAfterDispatch',
		];
	}
	
	/**
	 * Constructor
	 *
	 * @param          $subject
	 * @param   array  $config
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);
		JLoader::registerNamespace('\\AE\\Library\\Elodie\\', __DIR__ . '/classes/AE/Library/Elodie', false, false, 'psr4');
		
	}
	
	public function onAfterDispatch()
	{
		$this->processJsonApiDocument();
	}
	
	
	/**
	 * Do some processing on the jsonapidocument
	 * without altering the core code
	 *
	 * @since 0.1.0
	 */
	private function processJsonApiDocument()
	{
		if (!$this->app->isClient('api'))
		{
			return;
		}
		
		$doc = Factory::getDocument();
		
		if ($doc->getType() !== 'jsonapi')
		{
			return;
		}
		
		$uri                           = Uri::getInstance();
		$currentWebserviceResourceType = CommonHelper::getWebserviceResourceType($uri);
		$jinput                        = $this->app->input;
		$currentWebserviceResourceId   = $jinput->getUint('id', 0);
		$currentHttpVerb               = strtolower($jinput->getMethod() ?? 'get');
		
		if ($currentHttpVerb !== 'get')
		{
			return;
		}
		$isCollection = empty($currentWebserviceResourceId);
		$docArray     = $doc->toArray();
		$serializer   = new AlexApiSerializer($currentWebserviceResourceType);
		
		$element = $isCollection
			? new Collection(ArrayHelper::toObject(array_column($docArray['data'], 'attributes'), CMSObject::class), $serializer)
			: new Resource(ArrayHelper::toObject($docArray['data']['attributes'], CMSObject::class), $serializer);
		
		$doc->setData($element);
	}
}
