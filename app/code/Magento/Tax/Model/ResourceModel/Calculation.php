<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


/**
 * Tax Calculation Resource Model
 */
namespace Magento\Tax\Model\ResourceModel;

class Calculation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store ISO 3166-1 alpha-2 USA country code
     */
    const USA_COUNTRY_CODE = 'US';

    /**
     * Rates cache
     *
     * @var array
     */
    protected $_ratesCache = [];

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->_taxData = $taxData;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setMainTable('tax_calculation');
    }

    /**
     * Delete calculation settings by rule id
     *
     * @param int $ruleId
     * @return $this
     */
    public function deleteByRuleId($ruleId)
    {
        $conn = $this->getConnection();
        $where = $conn->quoteInto('tax_calculation_rule_id = ?', (int)$ruleId);
        $conn->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Retrieve distinct calculation
     *
     * @param  string $field
     * @param  int $ruleId
     * @return array
     */
    public function getCalculationsById($field, $ruleId)
    {
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), $field)->where('tax_calculation_rule_id = ?', (int)$ruleId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get tax rate information: calculation process data and tax rate
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     */
    public function getRateInfo($request)
    {
        $rates = $this->_getRates($request);
        return [
            'process' => $this->getCalculationProcess($request, $rates),
            'value' => $this->_calculateRate($rates)
        ];
    }

    /**
     * Get tax rate for specific tax rate request
     *
     * @param \Magento\Framework\DataObject $request
     * @return int
     */
    public function getRate($request)
    {
        return $this->_calculateRate($this->_getRates($request));
    }

    /**
     * Retrieve Calculation Process
     *
     * @param \Magento\Framework\DataObject $request
     * @param array|null $rates
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getCalculationProcess($request, $rates = null)
    {
        if ($rates === null) {
            $rates = $this->_getRates($request);
        }

        $result = [];
        $row = [];
        $ids = [];
        $currentRate = 0;
        $totalPercent = 0;
        $countedRates = count($rates);
        for ($i = 0; $i < $countedRates; $i++) {
            $rate = $rates[$i];
            $value = (isset($rate['value']) ? $rate['value'] : $rate['percent']) * 1;

            $oneRate = [
                'code' => $rate['code'],
                'title' => $rate['title'],
                'percent' => $value,
                'position' => $rate['position'],
                'priority' => $rate['priority']
            ];
            if (isset($rate['tax_calculation_rule_id'])) {
                $oneRate['rule_id'] = $rate['tax_calculation_rule_id'];
            }

            if (isset($rate['hidden'])) {
                $row['hidden'] = $rate['hidden'];
            }

            if (isset($rate['amount'])) {
                $row['amount'] = $rate['amount'];
            }

            if (isset($rate['base_amount'])) {
                $row['base_amount'] = $rate['base_amount'];
            }
            if (isset($rate['base_real_amount'])) {
                $row['base_real_amount'] = $rate['base_real_amount'];
            }
            $row['rates'][] = $oneRate;

            $ruleId = null;
            if (isset($rates[$i + 1]['tax_calculation_rule_id'])) {
                $ruleId = $rate['tax_calculation_rule_id'];
            }
            $priority = $rate['priority'];
            $ids[] = $rate['code'];

            if (isset($rates[$i + 1]['tax_calculation_rule_id'])) {
                while (isset($rates[$i + 1]) && $rates[$i + 1]['tax_calculation_rule_id'] == $ruleId) {
                    $i++;
                }
            }

            $currentRate += $value;

            if (!isset(
                $rates[$i + 1]
            ) || $rates[$i + 1]['priority'] != $priority || isset(
                $rates[$i + 1]['process']
            ) && $rates[$i + 1]['process'] != $rate['process']
            ) {
                if (!empty($rates[$i]['calculate_subtotal'])) {
                    $row['percent'] = $currentRate;
                    $totalPercent += $currentRate;
                } else {
                    $row['percent'] = $this->_collectPercent($totalPercent, $currentRate);
                    $totalPercent += $row['percent'];
                }
                $row['id'] = implode('', $ids);
                $result[] = $row;
                $row = [];
                $ids = [];

                $currentRate = 0;
            }
        }

        return $result;
    }

    /**
     * Return combined percent value
     *
     * @param float|int $percent
     * @param float|int $rate
     * @return float
     */
    protected function _collectPercent($percent, $rate)
    {
        return (100 + $percent) * ($rate / 100);
    }

    /**
     * Create search templates for postcode
     *
     * @param string $postcode
     * @param string|null $exactPostcode
     * @return string[]
     */
    protected function _createSearchPostCodeTemplates($postcode, $exactPostcode = null)
    {
        // as needed, reduce the postcode to the correct length
        $len = $this->_taxData->getPostCodeSubStringLength();
        $postcode = substr($postcode, 0, $len);

        // begin creating the search template array
        $strArr = [$postcode, $postcode . '*'];

        // if supplied, use the exact postcode as the basis for the search templates
        if ($exactPostcode) {
            $postcode = substr($exactPostcode, 0, $len);
            $strArr[] = $postcode;
        }

        // finish building out the search template array
        $strlen = strlen($postcode);
        for ($i = 1; $i < $strlen; $i++) {
            $strArr[] = sprintf('%s*', substr($postcode, 0, -$i));
        }

        return $strArr;
    }

    /**
     * Returns tax rates for request - either pereforms SELECT from DB, or returns already cached result
     * Notice that productClassId due to optimization can be array of ids
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getRates($request)
    {
        // Extract params that influence our SELECT statement and use them to create cache key
        $storeId = $this->_storeManager->getStore($request->getStore())->getId();
        $customerClassId = $request->getCustomerClassId();
        $countryId = $request->getCountryId();
        $regionId = $request->getRegionId();
        $postcode = $request->getPostcode();

        // Process productClassId as it can be array or usual value. Form best key for cache.
        $productClassId = $request->getProductClassId();
        $ids = is_array($productClassId) ? $productClassId : [$productClassId];
        foreach ($ids as $key => $val) {
            $ids[$key] = (int)$val; // Make it integer for equal cache keys even in case of null/false/0 values
        }
        $ids = array_unique($ids);
        sort($ids);
        $productClassKey = implode(',', $ids);

        // Form cache key and either get data from cache or from DB
        $cacheKey = implode(
            '|',
            [$storeId, $customerClassId, $productClassKey, $countryId, $regionId, $postcode]
        );

        if (!isset($this->_ratesCache[$cacheKey])) {
            // Make SELECT and get data
            $select = $this->getConnection()->select();
            $select->from(
                ['main_table' => $this->getMainTable()],
                [
                    'tax_calculation_rate_id',
                    'tax_calculation_rule_id',
                    'customer_tax_class_id',
                    'product_tax_class_id'
                ]
            )->where(
                'customer_tax_class_id = ?',
                (int)$customerClassId
            );
            if ($productClassId) {
                $select->where('product_tax_class_id IN (?)', $productClassId);
            }
            $ifnullTitleValue = $this->getConnection()->getCheckSql(
                'title_table.value IS NULL',
                'rate.code',
                'title_table.value'
            );
            $ruleTableAliasName = $this->getConnection()->quoteIdentifier('rule.tax_calculation_rule_id');
            $select->join(
                ['rule' => $this->getTable('tax_calculation_rule')],
                $ruleTableAliasName . ' = main_table.tax_calculation_rule_id',
                ['rule.priority', 'rule.position', 'rule.calculate_subtotal']
            )->join(
                ['rate' => $this->getTable('tax_calculation_rate')],
                'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id',
                [
                    'value' => 'rate.rate',
                    'rate.tax_country_id',
                    'rate.tax_region_id',
                    'rate.tax_postcode',
                    'rate.tax_calculation_rate_id',
                    'rate.code'
                ]
            )->joinLeft(
                ['title_table' => $this->getTable('tax_calculation_rate_title')],
                "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id " .
                "AND title_table.store_id = '{$storeId}'",
                ['title' => $ifnullTitleValue]
            )->where(
                'rate.tax_country_id = ?',
                $countryId
            )->where(
                "rate.tax_region_id IN(?)",
                [0, (int)$regionId]
            );
            $postcodeIsNumeric = is_numeric($postcode);
            $postcodeIsRange = false;
            $originalPostcode = null;
            if (is_string($postcode) && preg_match('/^(.+)-(.+)$/', $postcode, $matches)) {
                if ($countryId == self::USA_COUNTRY_CODE && is_numeric($matches[2]) && strlen($matches[2]) == 4) {
                    $postcodeIsNumeric = true;
                    $originalPostcode = $postcode;
                    $postcode = $matches[1];
                } else {
                    $postcodeIsRange = true;
                    $zipFrom = $matches[1];
                    $zipTo = $matches[2];
                }
            }

            if ($postcodeIsNumeric || $postcodeIsRange) {
                $selectClone = clone $select;
                $selectClone->where('rate.zip_is_range IS NOT NULL');
            }
            $select->where('rate.zip_is_range IS NULL');

            if ($postcode != '*' || $postcodeIsRange) {
                $select->where(
                    "rate.tax_postcode IS NULL OR rate.tax_postcode IN('*', '', ?)",
                    $postcodeIsRange ? $postcode : $this->_createSearchPostCodeTemplates($postcode, $originalPostcode)
                );
                if ($postcodeIsNumeric) {
                    $selectClone->where('? BETWEEN rate.zip_from AND rate.zip_to', $postcode);
                } elseif ($postcodeIsRange) {
                    $selectClone->where('rate.zip_from >= ?', $zipFrom)
                        ->where('rate.zip_to <= ?', $zipTo);
                }
            }

            /**
             * @see ZF-7592 issue http://framework.zend.com/issues/browse/ZF-7592
             */
            if ($postcodeIsNumeric || $postcodeIsRange) {
                $select = $this->getConnection()->select()->union(
                    ['(' . $select . ')', '(' . $selectClone . ')']
                );
            }

            $select->order(
                'priority ' . \Magento\Framework\DB\Select::SQL_ASC
            )->order(
                'tax_calculation_rule_id ' . \Magento\Framework\DB\Select::SQL_ASC
            )->order(
                'tax_country_id ' . \Magento\Framework\DB\Select::SQL_DESC
            )->order(
                'tax_region_id ' . \Magento\Framework\DB\Select::SQL_DESC
            )->order(
                'tax_postcode ' . \Magento\Framework\DB\Select::SQL_DESC
            )->order(
                'value ' . \Magento\Framework\DB\Select::SQL_DESC
            );

            $fetchResult = $this->getConnection()->fetchAll($select);
            $filteredRates = [];
            if ($fetchResult) {
                foreach ($fetchResult as $rate) {
                    if (!isset($filteredRates[$rate['tax_calculation_rate_id']])) {
                        $filteredRates[$rate['tax_calculation_rate_id']] = $rate;
                    }
                }
            }
            $this->_ratesCache[$cacheKey] = array_values($filteredRates);
        }

        return $this->_ratesCache[$cacheKey];
    }

    /**
     * Calculate rate
     *
     * @param array $rates
     * @return int
     */
    protected function _calculateRate($rates)
    {
        $result = 0;
        $currentRate = 0;
        $countedRates = count($rates);
        for ($i = 0; $i < $countedRates; $i++) {
            $rate = $rates[$i];
            $rule = $rate['tax_calculation_rule_id'];
            $value = $rate['value'];
            $priority = $rate['priority'];

            while (isset($rates[$i + 1]) && $rates[$i + 1]['tax_calculation_rule_id'] == $rule) {
                $i++;
            }

            $currentRate += $value;

            if (!isset($rates[$i + 1]) || $rates[$i + 1]['priority'] != $priority) {
                if (!empty($rates[$i]['calculate_subtotal'])) {
                    $result += $currentRate;
                } else {
                    $result += $this->_collectPercent($result, $currentRate);
                }
                $currentRate = 0;
            }
        }

        return $result;
    }

    /**
     * Retrieve rate ids
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     */
    public function getRateIds($request)
    {
        $result = [];
        $rates = $this->_getRates($request);
        $countedRates = count($rates);
        for ($i = 0; $i < $countedRates; $i++) {
            $rate = $rates[$i];
            $rule = $rate['tax_calculation_rule_id'];
            $result[] = $rate['tax_calculation_rate_id'];
            while (isset($rates[$i + 1]) && $rates[$i + 1]['tax_calculation_rule_id'] == $rule) {
                $i++;
            }
        }

        return $result;
    }
}
