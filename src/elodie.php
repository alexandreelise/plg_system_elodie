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

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Response\JsonResponse;

/**
 * Elodie plugin.
 *
 * @package   Elodie
 * @since     0.1.0
 */
class PlgSystemElodie extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  0.1.0
	 */
	protected $app;
	
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  0.1.0
	 */
	protected $db;
	
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  0.1.0
	 */
	protected $autoloadLanguage = true;
	
	
	/**
	 * onAfterRoute.
	 *
	 * @return  void
	 *
	 * @since   0.1.0
	 */
	public function onAfterRoute()
	{
		if ($this->app->isClient('administrator'))
		{
			return;
		}
		
		$currentJoomlaUserId = (int) Factory::getUser()->id;
		$isNotMe             = ((class_exists('CoopaclAccess')
				&& method_exists('CoopaclAccess', 'getViewLevelTitleFromId')
				&& method_exists('CoopaclAccess', 'computeUserBasedAccessLevel'))
			&& !CoopaclAccess::computeUserBasedAccessLevel($currentJoomlaUserId, ['Me']));
		$isNotSuperUser      = !Access::check($currentJoomlaUserId, 'core.manage');
		
		if ($isNotMe || $isNotSuperUser)
		{
			return;
		}
		
		if ((int) $this->app->input->getUint('_enable_proxy', 0) !== 1)
		{
			return;
		}
		
		self::AlexApiProxy($this->app, $this->params->get('baseUrl'), $this->params->get('token'));
	}
	
	/**
	 * Transform request on the fly and return an altered response with custom functionality added
	 *
	 * @param   \Joomla\CMS\Application\CMSApplication|null  $givenApp
	 * @param   string|null                                  $givenBaseUrl
	 * @param   string|null                                  $givenToken
	 *
	 */
	private static function AlexApiProxy(
		?CMSApplication $givenApp = null,
		?string         $givenBaseUrl = null,
		?string         $givenToken = null
	)
	{
		header('Content-Type: application/vnd.api+json; charset=utf-8');
		try
		{
			$baseUrl           = $givenBaseUrl ?? 'https://j4x.alexapi.cloud';
			$app               = $givenApp ?? Factory::getApplication();
			$webserviceVersion = $app->input->getString('webservice_version', '');
			
			$verb       = strtolower($app->input->getWord('verb', $app->input->getMethod()));
			$data       = $app->input->get('data', [], 'ARRAY');
			$resource   = $app->input->getString('resource', '');
			$resourceId = $app->input->getUint('resource_id', 0);
			
			$includeQueryString        = $app->input->get('include', '', 'STRING');
			$sparseFieldsetQueryString = $app->input->get('fields', [], 'ARRAY');
			$pageQueryString           = $app->input->get('page', [], 'ARRAY');
			$filterQueryString         = $app->input->get('filter', [], 'ARRAY');
			$sortQueryString           = $app->input->get('sort', '', 'STRING');
			$listQueryString           = $app->input->get('list', [], 'ARRAY');
			
			$allowedQueryString = http_build_query(array_merge(
					empty($filterQueryString) ? [] : ['filter' => $filterQueryString],
					empty($pageQueryString) ? [] : ['page' => $pageQueryString],
					empty($includeQueryString) ? [] : ['include' => $includeQueryString],
					empty($sparseFieldsetQueryString) ? [] : ['fields' => $sparseFieldsetQueryString],
					empty($sortQueryString) ? [] : ['sort' => $sortQueryString],
					empty($listQueryString) ? [] : ['list' => $listQueryString])
			);
			
			$resourcesFilename = JPATH_ROOT . '/media/plg_system_elodie/data/resources.json';
			$allowedResources  = [];
			if (file_exists($resourcesFilename))
			{
				$resourcesDecoded = json_decode(file_get_contents($resourcesFilename), true);
				$allowedResources = $resourcesDecoded['data'] ?? [];
			}
			
			if (!in_array($resource, $allowedResources, true))
			{
				$resource   = '';
				$resourceId = 0;
			}
			
			$currentResourceId = empty($resourceId) ? '' : '/' . $resourceId;
			
			$currentAllowedQueryString = empty($allowedQueryString) ? '' : '?' . $allowedQueryString;
			
			$http     = new Http();
			$headers  = [
				'X-Joomla-Token' => $givenToken ?? '',
				'Content-Type'   => 'application/vnd.api+json; charset=utf-8',
			];
			$url      = sprintf('%s%s%s%s%s', $baseUrl, $webserviceVersion, $resource, $currentResourceId, $currentAllowedQueryString);
			$response = $http->$verb($url, $headers);
			
			if (in_array($verb, ['post', 'put', 'patch'], true))
			{
				$url      = sprintf('%s%s%s%s', $baseUrl, $webserviceVersion, $resource, $currentResourceId);
				$response = $http->$verb($url, $data, $headers);
			}
			
			$assocResponse = json_decode($response->body, true);
			$it1           = new RecursiveArrayIterator($assocResponse);
			$it2           = new RecursiveIteratorIterator($it1, RecursiveIteratorIterator::SELF_FIRST);
			
			$output       = [];
			$chosenFields = null;
			if ($parts = explode('/', $resource))
			{
				$resourceType = $parts[max(0, (count($parts) - 1))];
				$chosenFields = array_values(array_filter(explode(',', $sparseFieldsetQueryString[$resourceType] ?? '')));
			}
			
			$isSparse = !empty($chosenFields);
			
			foreach ($it2 as $key => $item)
			{
				if ($key === 'attributes')
				{
					$processed = [];
					//could modify it directly using reference operator but to me it's less clean
					if ($isSparse)
					{
						foreach ($item as $itemKey => $itemValue)
						{
							if (!in_array($itemKey, $chosenFields, true))
							{
								continue;
							}
							$processed[$itemKey] = $itemValue;
						}
					}
					else
					{
						$processed = $item;
					}
					// read all attibutes key/value pair k => v
					if (empty($filterQueryString))
					{
						$output[][$key] = $processed;
					}
					else
					{
						foreach ($processed as $k => $v)
						{
							// try to do fuzzy matching using levenstein distance algorithm
							if ((is_string($v)
									&& is_string($filterQueryString[$k])
									&& levenshtein($filterQueryString[$k], $v, 1, 2, 4) < 7
								)
								|| ($filterQueryString[$k] === $v))
							{
								$output[][$key] = $processed;
							}
						}
					}
				}
			}
			foreach ($assocResponse['data'] as $index => $assocItem)
			{
				if (empty($output[$index]['attributes']))
				{
					unset($assocResponse['data'][$index]);
					continue;
				}
				$assocResponse['data'][$index]['attributes'] = $output[$index]['attributes'];
				
			}
			
			
			$outcome = $assocResponse;
		}
		catch (Throwable $throwable)
		{
			$outcome = $throwable;
		}
		
		echo new JsonResponse((is_array($outcome) || is_object($outcome)) ? $outcome : []);
		exit();
		
	}
}
