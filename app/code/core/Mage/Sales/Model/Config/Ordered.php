<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration class for ordered items
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Config_Ordered extends Mage_Core_Model_Config_Base
{
    /**
     * Cache key for collectors
     *
     * @var string|null
     */
    protected $_collectorsCacheKey = null;

    /**
     * Configuration path where to collect registered totals
     *
     * @var string|null
     */
    protected $_totalsConfigNode = null;

    /**
     * Prepared models
     *
     * @var array
     */
    protected $_models = array();

    /**
     * Models configuration
     *
     * @var array
     */
    protected $_modelsConfig = array();

    /**
     * Sorted models
     *
     * @var array
     */
    protected $_collectors = array();

    /**
     * Initialize total models configuration and objects
     *
     * @return Mage_Sales_Model_Config_Ordered
     */
    protected function _initModels()
    {
        $totalsConfig = $this->getNode($this->_totalsConfigNode);

        foreach ($totalsConfig->children() as $totalCode => $totalConfig) {
            $class = $totalConfig->getClassName();
            if (!empty($class)) {
                $this->_models[$totalCode] = $this->_initModelInstance($class, $totalCode, $totalConfig);
            }
        }
        return $this;
    }

    /**
     * Init model class by configuration
     *
     * @abstract
     * @param string $class
     * @param string $totalCode
     * @param array $totalConfig
     * @return mixed
     */
    abstract protected function _initModelInstance($class, $totalCode, $totalConfig);

    /**
     * Prepare configuration array for total model
     *
     * @param   string $code
     * @param   Mage_Core_Model_Config_Element $totalConfig
     * @return  array
     */
    protected function _prepareConfigArray($code, $totalConfig)
    {
        $totalConfig = (array)$totalConfig;
        if (isset($totalConfig['before'])) {
            $totalConfig['before'] = explode(',', $totalConfig['before']);
        } else {
            $totalConfig['before'] = array();
        }
        if (isset($totalConfig['after'])) {
            $totalConfig['after'] = explode(',', $totalConfig['after']);
        } else {
            $totalConfig['after'] = array();
        }
        $totalConfig['_code'] = $code;
        return $totalConfig;
    }

    /**
     * Aggregate before/after information from all items and sort totals based on this data
     *
     * @param array $config
     * @return array
     */
    protected function _getSortedCollectorCodes(array $config)
    {
        // invoke simple sorting if the first element contains the "sort_order" key
        reset($config);
        $element = current($config);
        if (isset($element['sort_order'])) {
            uasort($config, array($this, '_compareSortOrder'));
            $result = array_keys($config);
        } else {
            $result = array_keys($config);
            // Move all totals with before specification in front of related total
            foreach ($config as $code => &$data) {
                foreach ($data['before'] as $positionCode) {
                    if (!isset($config[$positionCode])) {
                        continue;
                    }
                    if (!in_array($code, $config[$positionCode]['after'], true)) {
                        // Also add additional after condition for related total,
                        // to keep it always after total with before value specified
                        $config[$positionCode]['after'][] = $code;
                    }
                    $currentPosition = array_search($code, $result, true);
                    $desiredPosition = array_search($positionCode, $result, true);
                    if ($currentPosition > $desiredPosition) {
                        // Only if current position is not corresponding to before condition
                        array_splice($result, $currentPosition, 1); // Removes existent
                        array_splice($result, $desiredPosition, 0, $code); // Add at new position
                    }
                }
            }
            // Sort out totals with after position specified
            foreach ($config as $code => &$data) {
                $maxAfter = null;
                $currentPosition = array_search($code, $result, true);

                foreach ($data['after'] as $positionCode) {
                    $maxAfter = max($maxAfter, array_search($positionCode, $result, true));
                }

                if ($maxAfter !== null && $maxAfter > $currentPosition) {
                    // Moves only if it is in front of after total
                    array_splice($result, $maxAfter + 1, 0, $code); // Add at new position
                    array_splice($result, $currentPosition, 1); // Removes existent
                }
            }
        }
        return $result;
    }

    /**
     * Initialize collectors array.
     * Collectors array is array of total models ordered based on configuration settings
     *
     * @return  Mage_Sales_Model_Config_Ordered
     */
    protected function _initCollectors()
    {
        $useCache = Mage::app()->useCache('config');
        $sortedCodes = array();
        if ($useCache) {
            $cachedData = Mage::app()->loadCache($this->_collectorsCacheKey);
            if ($cachedData) {
                $sortedCodes = unserialize($cachedData);
            }
        }
        if (!$sortedCodes) {
            try {
                self::validateCollectorDeclarations($this->_modelsConfig);
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $sortedCodes = $this->_getSortedCollectorCodes($this->_modelsConfig);
            if ($useCache) {
                Mage::app()->saveCache(serialize($sortedCodes), $this->_collectorsCacheKey, array(
                    Mage_Core_Model_Config::CACHE_TAG
                ));
            }
        }
        foreach ($sortedCodes as $code) {
            $this->_collectors[$code] = $this->_models[$code];
        }

        return $this;
    }

    /**
     * Callback that uses sort_order for comparison
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _compareSortOrder($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }
        if ($a['sort_order'] > $b['sort_order']) {
            $res = 1;
        } elseif ($a['sort_order'] < $b['sort_order']) {
            $res = -1;
        } else {
            $res = 0;
        }
        return $res;
    }

    /**
     * Validate specified configuration array as sales totals declaration
     *
     * If there are contradictions, the totals cannot be sorted correctly. Possible contradictions:
     * - A relation between totals leads to cycles
     * - Two relations combined lead to cycles
     *
     * @param array $config
     * @throws Magento_Exception
     */
    public static function validateCollectorDeclarations($config)
    {
        $before = self::_instantiateGraph($config, 'before');
        $after  = self::_instantiateGraph($config, 'after');
        foreach ($after->getRelations(Magento_Data_Graph::INVERSE) as $from => $relations) {
            foreach ($relations as $to) {
                $before->addRelation($from, $to);
            }
        }
        $cycle = $before->findCycle();
        if ($cycle) {
            throw new Magento_Exception(sprintf(
                'Found cycle in sales total declarations: %s', implode(' -> ', $cycle)
            ));
        }
    }

    /**
     * Parse "config" array by specified key and instantiate a graph based on that
     *
     * @param array $config
     * @param string $key
     * @return Magento_Data_Graph
     */
    private static function _instantiateGraph($config, $key)
    {
        $nodes = array_keys($config);
        $graph = array();
        foreach ($config as $from => $row) {
            foreach ($row[$key] as $to) {
                $graph[] = array($from, $to);
            }
        }
        return new Magento_Data_Graph($nodes, $graph);
    }
}
