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
 * @category    Magento
 * @package     Magento_Weee
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Weee\Model;

class Tax extends \Magento\Core\Model\AbstractModel
{
    /**
     * Including FPT only
     */
    const DISPLAY_INCL              = 0;
    /**
     * Including FPT and FPT description
     */
    const DISPLAY_INCL_DESCR        = 1;
    /**
     * Excluding FPT, FPT description, final price
     */
    const DISPLAY_EXCL_DESCR_INCL   = 2;
    /**
     * Excluding FPT
     */
    const DISPLAY_EXCL              = 3;

    protected $_allAttributes = null;
    protected $_productDiscounts = array();

    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData = null;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Tax\Model\CalculationFactory
     */
    protected $_calculationFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Model\CalculationFactory $calculationFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Weee\Model\Resource\Tax $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\CalculationFactory $calculationFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Weee\Helper\Data $weeeData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Weee\Model\Resource\Tax $resource,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_storeManager = $storeManager;
        $this->_calculationFactory = $calculationFactory;
        $this->_customerSession = $customerSession;
        $this->_taxData = $taxData;
        $this->_weeeData = $weeeData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Magento\Weee\Model\Resource\Tax');
    }

    public function getWeeeAmount(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = false,
        $ignoreDiscount = false)
    {
        $amount = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTax,
            $ignoreDiscount
        );
        foreach ($attributes as $attribute) {
            $amount += $attribute->getAmount();
        }
        return $amount;
    }

    public function getWeeeAttributeCodes($forceEnabled = false)
    {
        return $this->getWeeeTaxAttributeCodes($forceEnabled);
    }

    /**
     * Retrieve Wee tax attribute codes
     *
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeTaxAttributeCodes($forceEnabled = false)
    {
        if (!$forceEnabled && !$this->_weeeData->isEnabled()) {
            return array();
        }

        if (is_null($this->_allAttributes)) {
            $this->_allAttributes = $this->_attributeFactory->create()->getAttributeCodesByFrontendType('weee');
        }
        return $this->_allAttributes;
    }

    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $ignoreDiscount = false)
    {
        $result = array();
        $allWeee = $this->getWeeeTaxAttributeCodes();
        if (!$allWeee) {
            return $result;
        }

        $websiteId = $this->_storeManager->getWebsite($website)->getId();
        $store = $this->_storeManager->getWebsite($website)->getDefaultGroup()->getDefaultStore();

        $customer = null;
        if ($shipping) {
            $customerTaxClass = $shipping->getQuote()->getCustomerTaxClassId();
            $customer = $shipping->getQuote()->getCustomer();
        } else {
            $customerTaxClass = null;
        }

        /** @var \Magento\Tax\Model\Calculation $calculator */
        $calculator = $this->_calculationFactory->create();
        if ($customer) {
            $calculator->setCustomer($customer);
        }
        $rateRequest = $calculator->getRateRequest($shipping, $billing, $customerTaxClass, $store);
        $defaultRateRequest = $calculator->getRateRequest(false, false, false, $store);

        $discountPercent = 0;
        if (!$ignoreDiscount && $this->_weeeData->isDiscounted($store)) {
            $discountPercent = $this->_getDiscountPercentForProduct($product);
        }

        $productAttributes = $product->getTypeInstance()->getSetAttributes($product);
        foreach ($productAttributes as $code => $attribute) {
            if (in_array($code, $allWeee)) {

                $attributeSelect = $this->getResource()->getReadConnection()->select();
                $attributeSelect
                    ->from($this->getResource()->getTable('weee_tax'), 'value')
                    ->where('attribute_id = ?', (int)$attribute->getId())
                    ->where('website_id IN(?)', array($websiteId, 0))
                    ->where('country = ?', $rateRequest->getCountryId())
                    ->where('state IN(?)', array($rateRequest->getRegionId(), '*'))
                    ->where('entity_id = ?', (int)$product->getId())
                    ->limit(1);

                $order = array('state ' . \Magento\DB\Select::SQL_DESC, 'website_id ' . \Magento\DB\Select::SQL_DESC);
                $attributeSelect->order($order);

                $value = $this->getResource()->getReadConnection()->fetchOne($attributeSelect);
                if ($value) {
                    if ($discountPercent) {
                        $value = $this->_storeManager->getStore()->roundPrice($value-($value*$discountPercent/100));
                    }

                    $taxAmount = $amount = 0;
                    $amount    = $value;
                    if ($calculateTax && $this->_weeeData->isTaxable($store)) {
                        /** @var \Magento\Tax\Model\Calculation $calculator */
                        $defaultPercent = $this->_calculationFactory->create()
                            ->getRate(
                                $defaultRateRequest->setProductClassId($product->getTaxClassId())
                            );
                        $currentPercent = $product->getTaxPercent();
                        if ($this->_taxData->priceIncludesTax($store)) {
                            $taxAmount = $this->_storeManager->getStore()
                                ->roundPrice($value/(100+$defaultPercent)*$currentPercent);
                        } else {
                            $taxAmount = $this->_storeManager->getStore()->roundPrice($value*$defaultPercent/100);
                        }
                    }

                    $one = new \Magento\Object();
                    $one->setName(__($attribute->getFrontend()->getLabel()))
                        ->setAmount($amount)
                        ->setTaxAmount($taxAmount)
                        ->setCode($attribute->getAttributeCode());

                    $result[] = $one;
                }
            }
        }
        return $result;
    }

    protected function _getDiscountPercentForProduct($product)
    {
        $website = $this->_storeManager->getStore()->getWebsiteId();
        $group = $this->_customerSession->getCustomerGroupId();
        $key = implode('-', array($website, $group, $product->getId()));
        if (!isset($this->_productDiscounts[$key])) {
            $this->_productDiscounts[$key] = (int) $this->getResource()
                ->getProductDiscountPercent($product->getId(), $website, $group);
        }
        if ($value = $this->_productDiscounts[$key]) {
            return 100-min(100, max(0, $value));
        } else {
            return 0;
        }
    }

    /**
     * Update discounts for FPT amounts of all products
     *
     * @return \Magento\Weee\Model\Tax
     */
    public function updateDiscountPercents()
    {
        $this->getResource()->updateDiscountPercents();
        return $this;
    }

    /**
     * Update discounts for FPT amounts base on products condiotion
     *
     * @param  mixed $products
     * @return \Magento\Weee\Model\Tax
     */
    public function updateProductsDiscountPercent($products)
    {
        $this->getResource()->updateProductsDiscountPercent($products);
        return $this;
    }
}
