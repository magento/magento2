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
 * Address Total Collector model
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Quote_Address_Total_Collector
{
    /**
     * Path to sort order values of checkout totals
     */
    const XML_PATH_SALES_TOTALS_SORT = 'sales/totals_sort';

    /**
     * Total models array
     *
     * @var array
     */
    protected $_models = array();

    /**
     * Total models configuration
     *
     * @var array
     */
    protected $_modelsConfig = array();

    /**
     * Total models array ordered for right calculation logic
     *
     * @var array
     */
    protected $_collectors = array();

    /**
     * Total models array ordered for right display sequence
     *
     * @var array
     */
    protected $_retrievers = array();

    /**
     * Corresponding store object
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Configuration path where to collect registered totals
     *
     * @var string
     */
    protected $_totalsConfigNode = 'global/sales/quote/totals';

    /**
     * Cache key for collectors
     *
     * @var string
     */
    protected $_collectorsCacheKey = 'sorted_quote_collectors';

    /**
     * Init corresponding total models
     *
     * @param array $options
     */
    public function __construct($options)
    {
        if (isset($options['store'])) {
            $this->_store = $options['store'];
        } else {
            $this->_store = Mage::app()->getStore();
        }
        $this->_initModels()
            ->_initCollectors()
            ->_initRetrievers();
    }

    /**
     * Get total models array ordered for right calculation logic
     *
     * @return array
     */
    public function getCollectors()
    {
        return $this->_collectors;
    }

    /**
     * Get total models array ordered for right display sequence
     *
     * @return array
     */
    public function getRetrievers()
    {
        return $this->_retrievers;
    }

    /**
     * Initialize total models configuration and objects
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Collector
     */
    protected function _initModels()
    {
        $totalsConfig = Mage::getConfig()->getNode($this->_totalsConfigNode);

        foreach ($totalsConfig->children() as $totalCode=>$totalConfig) {
            $class = $totalConfig->getClassName();
            if ($class) {
                $model = Mage::getModel($class);
                if ($model instanceof Mage_Sales_Model_Quote_Address_Total_Abstract) {
                    $model->setCode($totalCode);
                    $this->_modelsConfig[$totalCode]= $this->_prepareConfigArray($totalCode, $totalConfig);
                    $this->_modelsConfig[$totalCode]= $model->processConfigArray(
                        $this->_modelsConfig[$totalCode],
                        $this->_store
                    );
                    $this->_models[$totalCode]      = $model;
                } else {
                    Mage::throwException(
                        Mage::helper('Mage_Sales_Helper_Data')->__('The address total model should be extended from Mage_Sales_Model_Quote_Address_Total_Abstract.')
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Prepare configuration array for total model
     *
     * @param   string $code
     * @param   Mage_Core_Model_Config_Element $totalConfig
     * @return  array
     */
    protected function _prepareConfigArray($code, $totalConfig)
    {
        $totalConfig = (array) $totalConfig;
        if (isset($totalConfig['before'])) {
            $totalConfig['before'] = explode(',',$totalConfig['before']);
        } else {
            $totalConfig['before'] = array();
        }
        if (isset($totalConfig['after'])) {
            $totalConfig['after'] = explode(',',$totalConfig['after']);
        } else {
            $totalConfig['after'] = array();
        }
        $totalConfig['_code'] = $code;
        return $totalConfig;
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
        reset($configArray); $element = current($configArray);
        if (isset($element['sort_order'])) {
            uasort($configArray, array($this, '_compareSortOrder'));
        } else {
            foreach ($configArray as $code => $data) {
                foreach ($data['before'] as $beforeCode) {
                    if (!isset($configArray[$beforeCode])) {
                        continue;
                    }
                    $configArray[$code]['before'] = array_merge(
                        $configArray[$code]['before'], $configArray[$beforeCode]['before']
                    );
                    $configArray[$beforeCode]['after']  = array_merge(
                        $configArray[$beforeCode]['after'], array($code), $data['after']
                    );
                    $configArray[$beforeCode]['after']  = array_unique($configArray[$beforeCode]['after']);
                }
                foreach ($data['after'] as $afterCode) {
                    if (!isset($configArray[$afterCode])) {
                        continue;
                    }
                    $configArray[$code]['after'] = array_merge(
                        $configArray[$code]['after'], $configArray[$afterCode]['after']
                    );
                    $configArray[$afterCode]['before'] = array_merge(
                        $configArray[$afterCode]['before'], array($code), $data['before']
                    );
                    $configArray[$afterCode]['before'] = array_unique($configArray[$afterCode]['before']);
                }
            }
            uasort($configArray, array($this, '_compareTotals'));
        }
        $sortedCollectors = array_keys($configArray);
        if (Mage::app()->useCache('config')) {
            Mage::app()->saveCache(serialize($sortedCollectors), $this->_collectorsCacheKey, array(
                Mage_Core_Model_Config::CACHE_TAG
            ));
        }
        return $sortedCollectors;
    }

    /**
     * Initialize collectors array.
     * Collectors array is array of total models ordered based on configuration settings
     *
     * @return  Mage_Sales_Model_Quote_Address_Total_Collector
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
     * uasort callback function
     *
     * @param   array $a
     * @param   array $b
     * @return  int
     */
    protected function _compareTotals($a, $b)
    {
        $aCode = $a['_code'];
        $bCode = $b['_code'];
        if (in_array($aCode, $b['after']) || in_array($bCode, $a['before'])) {
            $res = -1;
        } elseif (in_array($bCode, $a['after']) || in_array($aCode, $b['before'])) {
            $res = 1;
        } else {
            $res = 0;
        }
        return $res;
    }

    /**
     * uasort() callback that uses sort_order for comparison
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
     * Initialize retrievers array
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Collector
     */
    protected function _initRetrievers()
    {
        $sorts = Mage::getStoreConfig(self::XML_PATH_SALES_TOTALS_SORT, $this->_store);
        foreach ($sorts as $code => $sortOrder) {
            if (isset($this->_models[$code])) {
                // Reserve enough space for collisions
                $retrieverId = 100 * (int) $sortOrder;
                // Check if there is a retriever with such id and find next available position if needed
                while (isset($this->_retrievers[$retrieverId])) {
                    $retrieverId++;
                }
                $this->_retrievers[$retrieverId] = $this->_models[$code];
            }
        }
        ksort($this->_retrievers);
        $notSorted = array_diff(array_keys($this->_models), array_keys($sorts));
        foreach ($notSorted as $code) {
            $this->_retrievers[] = $this->_models[$code];
        }
        return $this;
    }
}
