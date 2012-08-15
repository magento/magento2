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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
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
     * Topological sort
     *
     * @param $nodeids Node Ids - example: array('subtotal','grand_total');
     * @param $edges Array of Edges. Each edge is specified as an array with two elements: The source and destination node of the edge
     *               Example: array(array('subtotal','grand_total')); -> subtotal comes before grand_total
     * @return array|null
     */
    public function _topologicalSort($nodeids, $edges) {
        $L = $S = $nodes = array();
        foreach($nodeids as $id) {
            $nodes[$id] = array('in'=>array(), 'out'=>array());
            foreach($edges as $e) {
                if ($id==$e[0]) { $nodes[$id]['out'][]=$e[1]; }
                if ($id==$e[1]) { $nodes[$id]['in'][]=$e[0]; }
            }
        }
        foreach ($nodes as $id=>$n) { if (empty($n['in'])) $S[]=$id; }
        while ($id = array_shift($S)) {
            if (!in_array($id, $L)) {
                $L[] = $id;
                foreach($nodes[$id]['out'] as $m) {
                    $nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
                    if (empty($nodes[$m]['in'])) { $S[] = $m; }
                }
                $nodes[$id]['out'] = array();
            }
        }
        foreach($nodes as $n) {
            if (!empty($n['in']) or !empty($n['out'])) {
                return null; // not sortable as graph is cyclic
            }
        }
        return $L;
    }

    /**
     * Aggregate before/after information from all items and sort totals based on this data
     *
     * @return array
     */
    protected function _getSortedCollectorCodes()
    {
        if (Mage::app()->useCache('config')) {
            $cachedData = Mage::app()->loadCache($this->_collectorsCacheKey);
            if ($cachedData) {
                return unserialize($cachedData);
            }
        }
        $configArray = $this->_modelsConfig;
        // invoke simple sorting if the first element contains the "sort_order" key
        reset($configArray);
        $element = current($configArray);
        if (isset($element['sort_order'])) {
            uasort($configArray, array($this, '_compareSortOrder'));
            $sortedCollectors = array_keys($configArray);
        } else {
            // prepare data for topological sort
            $nodes = array_keys($configArray);
            $edges = array();

            foreach ($configArray as $data) {
                $_code = $data['_code'];
                if (!isset($configArray[$_code])) continue;
                foreach ($data['before'] as $beforeCode) {
                    if (!isset($configArray[$beforeCode])) continue;
                    $edges[] = array($_code, $beforeCode);
                }
                foreach ($data['after'] as $afterCode) {
                    if (!isset($configArray[$afterCode])) continue;
                    $edges[] = array($afterCode, $_code);
                }
            }
            $sortedCollectors = $this->_topologicalSort($nodes, $edges);

            if (is_null($sortedCollectors)) {
                throw new Mage_Sales_Exception('Total ordering before/after conditions can not be complied with');
            }
        }
        if (Mage::app()->useCache('config')) {
            Mage::app()->saveCache(serialize($sortedCollectors), $this->_collectorsCacheKey, array(
                    Mage_Core_Model_Config::CACHE_TAG
                )
            );
        }
        return $sortedCollectors;
    }

    /**
     * Initialize collectors array.
     * Collectors array is array of total models ordered based on configuration settings
     *
     * @return  Mage_Sales_Model_Config_Ordered
     */
    protected function _initCollectors()
    {
        $sortedCodes = $this->_getSortedCollectorCodes();
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
}
