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
namespace Magento\Weee\Model\Attribute\Backend\Weee;

use Magento\Framework\Model\Exception;

class Tax extends \Magento\Catalog\Model\Product\Attribute\Backend\Price
{
    /**
     * @var \Magento\Weee\Model\Resource\Attribute\Backend\Weee\Tax
     */
    protected $_attributeTax;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Weee\Model\Resource\Attribute\Backend\Weee\Tax $attributeTax
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Weee\Model\Resource\Attribute\Backend\Weee\Tax $attributeTax
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_storeManager = $storeManager;
        $this->_attributeTax = $attributeTax;
        parent::__construct($logger, $currencyFactory, $storeManager, $catalogData, $config);
    }

    /**
     * @return string
     */
    public static function getBackendModelName()
    {
        return 'Magento\Weee\Model\Attribute\Backend\Weee\Tax';
    }

    /**
     * Validate data
     *
     * @param   \Magento\Catalog\Model\Product $object
     * @return  $this
     * @throws  Exception
     */
    public function validate($object)
    {
        $taxes = $object->getData($this->getAttribute()->getName());
        if (empty($taxes)) {
            return $this;
        }
        $dup = array();

        foreach ($taxes as $tax) {
            if (!empty($tax['delete'])) {
                continue;
            }

            $state = isset($tax['state']) ? $tax['state'] : '*';
            $key1 = implode('-', array($tax['website_id'], $tax['country'], $state));

            if (!empty($dup[$key1])) {
                throw new Exception(
                    __('We found a duplicate of website, country and state fields for a fixed product tax')
                );
            }
            $dup[$key1] = 1;
        }
        return $this;
    }

    /**
     * Assign WEEE taxes to product data
     *
     * @param   \Magento\Catalog\Model\Product $object
     * @return  $this
     */
    public function afterLoad($object)
    {
        $data = $this->_attributeTax->loadProductData($object, $this->getAttribute());

        foreach ($data as $i => $row) {
            if ($data[$i]['website_id'] == 0) {
                $rate = $this->_storeManager->getStore()->getBaseCurrency()->getRate(
                    $this->_directoryHelper->getBaseCurrencyCode()
                );
                if ($rate) {
                    $data[$i]['website_value'] = $data[$i]['value'] / $rate;
                } else {
                    unset($data[$i]);
                }
            } else {
                $data[$i]['website_value'] = $data[$i]['value'];
            }
        }
        $object->setData($this->getAttribute()->getName(), $data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($object)
    {
        $orig = $object->getOrigData($this->getAttribute()->getName());
        $current = $object->getData($this->getAttribute()->getName());
        if ($orig == $current) {
            return $this;
        }

        $this->_attributeTax->deleteProductData($object, $this->getAttribute());
        $taxes = $object->getData($this->getAttribute()->getName());

        if (!is_array($taxes)) {
            return $this;
        }

        foreach ($taxes as $tax) {
            if (empty($tax['price']) || empty($tax['country']) || !empty($tax['delete'])) {
                continue;
            }

            if (isset($tax['state']) && $tax['state']) {
                $state = $tax['state'];
            } else {
                $state = '*';
            }

            $data = array();
            $data['website_id'] = $tax['website_id'];
            $data['country'] = $tax['country'];
            $data['state'] = $state;
            $data['value'] = $tax['price'];
            $data['attribute_id'] = $this->getAttribute()->getId();

            $this->_attributeTax->insertProductData($object, $data);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete($object)
    {
        $this->_attributeTax->deleteProductData($object, $this->getAttribute());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return $this->_attributeTax->getTable('weee_tax');
    }
}
