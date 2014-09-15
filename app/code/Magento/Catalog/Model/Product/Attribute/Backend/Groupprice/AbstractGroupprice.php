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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend\Groupprice;

use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;

/**
 * Catalog product abstract group price backend attribute model
 */
abstract class AbstractGroupprice extends Price
{
    /**
     * Website currency codes and rates
     *
     * @var array
     */
    protected $_rates;

    /**
     * Error message when duplicates
     *
     * @abstract
     * @return string
     */
    abstract protected function _getDuplicateErrorMessage();

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Product\Type $catalogProductType
    ) {
        $this->_catalogProductType = $catalogProductType;
        parent::__construct($logger, $currencyFactory, $storeManager, $catalogData, $config);
    }

    /**
     * Retrieve websites currency rates and base currency codes
     *
     * @return array
     */
    protected function _getWebsiteCurrencyRates()
    {
        if (is_null($this->_rates)) {
            $this->_rates = array();
            $baseCurrency = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );
            foreach ($this->_storeManager->getWebsites() as $website) {
                /* @var $website \Magento\Store\Model\Website */
                if ($website->getBaseCurrencyCode() != $baseCurrency) {
                    $rate = $this->_currencyFactory->create()->load(
                        $baseCurrency
                    )->getRate(
                        $website->getBaseCurrencyCode()
                    );
                    if (!$rate) {
                        $rate = 1;
                    }
                    $this->_rates[$website->getId()] = array(
                        'code' => $website->getBaseCurrencyCode(),
                        'rate' => $rate
                    );
                } else {
                    $this->_rates[$website->getId()] = array('code' => $baseCurrency, 'rate' => 1);
                }
            }
        }
        return $this->_rates;
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice
     */
    abstract protected function _getResource();

    /**
     * Get additional unique fields
     *
     * @param array $objectArray
     * @return array
     */
    protected function _getAdditionalUniqueFields($objectArray)
    {
        return array();
    }

    /**
     * Whether group price value fixed or percent of original price
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $priceObject
     * @return bool
     */
    protected function _isPriceFixed($priceObject)
    {
        return $priceObject->isGroupPriceFixed();
    }

    /**
     * Validate group price data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Model\Exception
     * @return bool
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $priceRows = $object->getData($attribute->getName());
        if (empty($priceRows)) {
            return true;
        }

        // validate per website
        $duplicates = array();
        foreach ($priceRows as $priceRow) {
            if (!empty($priceRow['delete'])) {
                continue;
            }
            $compare = join(
                '-',
                array_merge(
                    array($priceRow['website_id'], $priceRow['cust_group']),
                    $this->_getAdditionalUniqueFields($priceRow)
                )
            );
            if (isset($duplicates[$compare])) {
                throw new \Magento\Framework\Model\Exception($this->_getDuplicateErrorMessage());
            }

            if (!preg_match('/^\d*(\.|,)?\d{0,4}$/i', $priceRow['price']) || $priceRow['price'] < 0) {
                return __('Group price must be a number greater than 0.');
            }

            $duplicates[$compare] = true;
        }

        // if attribute scope is website and edit in store view scope
        // add global group prices for duplicates find
        if (!$attribute->isScopeGlobal() && $object->getStoreId()) {
            $origGroupPrices = $object->getOrigData($attribute->getName());
            if ($origGroupPrices) {
                foreach ($origGroupPrices as $price) {
                    if ($price['website_id'] == 0) {
                        $compare = join(
                            '-',
                            array_merge(
                                array($price['website_id'], $price['cust_group']),
                                $this->_getAdditionalUniqueFields($price)
                            )
                        );
                        $duplicates[$compare] = true;
                    }
                }
            }
        }

        // validate currency
        $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default');
        $rates = $this->_getWebsiteCurrencyRates();
        foreach ($priceRows as $priceRow) {
            if (!empty($priceRow['delete'])) {
                continue;
            }
            if ($priceRow['website_id'] == 0) {
                continue;
            }

            $globalCompare = join(
                '-',
                array_merge(array(0, $priceRow['cust_group']), $this->_getAdditionalUniqueFields($priceRow))
            );
            $websiteCurrency = $rates[$priceRow['website_id']]['code'];

            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                throw new \Magento\Framework\Model\Exception($this->_getDuplicateErrorMessage());
            }
        }

        return true;
    }

    /**
     * Prepare group prices data for website
     *
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @return array
     */
    public function preparePriceData(array $priceData, $productTypeId, $websiteId)
    {
        $rates = $this->_getWebsiteCurrencyRates();
        $data = array();
        $price = $this->_catalogProductType->priceFactory($productTypeId);
        foreach ($priceData as $v) {
            $key = join('-', array_merge(array($v['cust_group']), $this->_getAdditionalUniqueFields($v)));
            if ($v['website_id'] == $websiteId) {
                $data[$key] = $v;
                $data[$key]['website_price'] = $v['price'];
            } else if ($v['website_id'] == 0 && !isset($data[$key])) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                if ($this->_isPriceFixed($price)) {
                    $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                    $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                }
            }
        }

        return $data;
    }

    /**
     * Assign group prices to product data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $storeId = $object->getStoreId();
        $websiteId = null;
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }

        $data = $this->_getResource()->loadPriceData($object->getId(), $websiteId);
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = CustomerGroupServiceInterface::CUST_GROUP_ALL;
            }
        }

        if (!$object->getData('_edit_mode') && $websiteId) {
            $data = $this->preparePriceData($data, $object->getTypeId(), $websiteId);
        }

        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterSave($object)
    {
        $websiteId = $this->_storeManager->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;

        $priceRows = $object->getData($this->getAttribute()->getName());
        if (empty($priceRows)) {
            if ($isGlobal) {
                $this->_getResource()->deletePriceData($object->getId());
            } else {
                $this->_getResource()->deletePriceData($object->getId(), $websiteId);
            }
            return $this;
        }

        $old = array();
        $new = array();

        // prepare original data for compare
        $origGroupPrices = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origGroupPrices)) {
            $origGroupPrices = array();
        }
        foreach ($origGroupPrices as $data) {
            if ($data['website_id'] > 0 || $data['website_id'] == '0' && $isGlobal) {
                $key = join(
                    '-',
                    array_merge(
                        array($data['website_id'], $data['cust_group']),
                        $this->_getAdditionalUniqueFields($data)
                    )
                );
                $old[$key] = $data;
            }
        }

        // prepare data for save
        foreach ($priceRows as $data) {
            $hasEmptyData = false;
            foreach ($this->_getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            $key = join(
                '-',
                array_merge(array($data['website_id'], $data['cust_group']), $this->_getAdditionalUniqueFields($data))
            );

            $useForAllGroups = $data['cust_group'] == CustomerGroupServiceInterface::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;

            $new[$key] = array_merge(
                array(
                    'website_id' => $data['website_id'],
                    'all_groups' => $useForAllGroups ? 1 : 0,
                    'customer_group_id' => $customerGroupId,
                    'value' => $data['price']
                ),
                $this->_getAdditionalUniqueFields($data)
            );
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        $productId = $object->getId();

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_getResource()->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new \Magento\Framework\Object($data);
                $price->setEntityId($productId);
                $this->_getResource()->savePriceData($price);

                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new \Magento\Framework\Object(array('value_id' => $old[$k]['price_id'], 'value' => $v['value']));
                    $this->_getResource()->savePriceData($price);

                    $isChanged = true;
                }
            }
        }

        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }

    /**
     * Retrieve data for update attribute
     *
     * @param  \Magento\Catalog\Model\Product $object
     * @return array
     */
    public function getAffectedFields($object)
    {
        $data = array();
        $groupPrices = (array)$object->getData($this->getAttribute()->getName());
        $tableName = $this->_getResource()->getMainTable();
        foreach ($groupPrices as $value) {
            $data[$tableName][] = array(
                'attribute_id' => $this->getAttribute()->getAttributeId(),
                'entity_id' => $object->getId(),
                'value_id' => $value['price_id']
            );
        }

        return $data;
    }
}
