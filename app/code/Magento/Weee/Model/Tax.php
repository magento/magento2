<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Website;
use Magento\Tax\Model\Calculation;
use Magento\Customer\Api\AccountManagementInterface;

class Tax extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Including FPT only
     */
    const DISPLAY_INCL = 0;

    /**
     * Including FPT and FPT description
     */
    const DISPLAY_INCL_DESCR = 1;

    /**
     * Excluding FPT, FPT description, final price
     */
    const DISPLAY_EXCL_DESCR_INCL = 2;

    /**
     * Excluding FPT
     */
    const DISPLAY_EXCL = 3;

    /**
     * @var array|null
     */
    protected $_allAttributes = null;

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
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * Weee config
     *
     * @var \Magento\Weee\Model\Config
     */
    protected $weeeConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Model\CalculationFactory $calculationFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Tax\Helper\Data $taxData
     * @param Resource\Tax $resource
     * @param Config $weeeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\CalculationFactory $calculationFactory,
        \Magento\Customer\Model\Session $customerSession,
        AccountManagementInterface $accountManagement,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Weee\Model\Resource\Tax $resource,
        \Magento\Weee\Model\Config $weeeConfig,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_storeManager = $storeManager;
        $this->_calculationFactory = $calculationFactory;
        $this->_customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->_taxData = $taxData;
        $this->weeeConfig = $weeeConfig;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Weee\Model\Resource\Tax');
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Framework\Object $shipping
     * @param null|false|\Magento\Framework\Object $billing
     * @param Website $website
     * @param bool $calculateTax
     * @return int
     */
    public function getWeeeAmount(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = false
    ) {
        $amount = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTax
        );
        foreach ($attributes as $attribute) {
            $amount += $attribute->getAmount();
        }
        return $amount;
    }

    /**
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeAttributeCodes($forceEnabled = false)
    {
        return $this->getWeeeTaxAttributeCodes(null, $forceEnabled);
    }

    /**
     * Retrieve Wee tax attribute codes
     *
     * @param  null|string|bool|int|Store $store
     * @param  bool $forceEnabled
     * @return array
     */
    public function getWeeeTaxAttributeCodes($store = null, $forceEnabled = false)
    {
        if (!$forceEnabled && !$this->weeeConfig->isEnabled($store)) {
            return [];
        }

        if (is_null($this->_allAttributes)) {
            $this->_allAttributes = $this->_attributeFactory->create()->getAttributeCodesByFrontendType('weee');
        }
        return $this->_allAttributes;
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Sales\Model\Quote\Address $shipping
     * @param null|false|\Magento\Sales\Model\Quote\Address $billing
     * @param Website $website
     * @param bool $calculateTax
     * @return \Magento\Framework\Object[]
     */
    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null
    ) {
        $result = [];

        $websiteId = $this->_storeManager->getWebsite($website)->getId();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getWebsite($website)->getDefaultGroup()->getDefaultStore();

        $allWeee = $this->getWeeeTaxAttributeCodes($store);
        if (!$allWeee) {
            return $result;
        }

        /** @var \Magento\Tax\Model\Calculation $calculator */
        $calculator = $this->_calculationFactory->create();

        if ($shipping && $shipping->getCountryId()) {
            $customerTaxClass = $shipping->getQuote()->getCustomerTaxClassId();
        } else {
            // if customer logged use it default shipping and billing address
            if ($customerId = $this->_customerSession->getCustomerId()) {
                $shipping = $this->accountManagement->getDefaultShippingAddress($customerId);
                $billing = $this->accountManagement->getDefaultBillingAddress($customerId);
            }
            $customerTaxClass = null;
        }

        $rateRequest = $calculator->getRateRequest(
            $shipping,
            $billing,
            $customerTaxClass,
            $store
        );
        $defaultRateRequest = $calculator->getDefaultRateRequest($store);

        $productAttributes = $product->getTypeInstance()->getSetAttributes($product);
        foreach ($productAttributes as $code => $attribute) {
            if (in_array($code, $allWeee)) {

                $attributeSelect = $this->getResource()->getReadConnection()->select();
                $attributeSelect->from(
                    $this->getResource()->getTable('weee_tax'),
                    'value'
                )->where(
                    'attribute_id = ?',
                    (int)$attribute->getId()
                )->where(
                    'website_id IN(?)',
                    [$websiteId, 0]
                )->where(
                    'country = ?',
                    $rateRequest->getCountryId()
                )->where(
                    'state IN(?)',
                    [$rateRequest->getRegionId(), '*']
                )->where(
                    'entity_id = ?',
                    (int)$product->getId()
                )->limit(
                    1
                );

                $order = ['state ' . \Magento\Framework\DB\Select::SQL_DESC,
                    'website_id ' . \Magento\Framework\DB\Select::SQL_DESC];
                $attributeSelect->order($order);

                $value = $this->getResource()->getReadConnection()->fetchOne($attributeSelect);
                if ($value) {
                    $taxAmount = $amount = 0;
                    $amount = $value;
                    if ($calculateTax && $this->weeeConfig->isTaxable($store)) {
                        /** @var \Magento\Tax\Model\Calculation $calculator */
                        $defaultPercent = $calculator->getRate(
                            $defaultRateRequest->setProductClassId($product->getTaxClassId())
                        );
                        $currentPercent = $calculator->getRate(
                            $rateRequest->setProductClassId($product->getTaxClassId())
                        );
                        if ($this->_taxData->priceIncludesTax($store)) {
                            $amountInclTax = $value / (100 + $defaultPercent) * (100 + $currentPercent);
                            //round the "golden price"
                            $amountInclTax = $this->priceCurrency->round($amountInclTax);
                            $taxAmount = $amountInclTax - $amountInclTax / (100 + $currentPercent) * 100;
                            $taxAmount = $this->priceCurrency->round($taxAmount);
                        } else {
                            $appliedRates = $this->_calculationFactory->create()->getAppliedRates($rateRequest);
                            if (count($appliedRates) > 1) {
                                $taxAmount = 0;
                                foreach ($appliedRates as $appliedRate) {
                                    $taxRate = $appliedRate['percent'];
                                    $taxAmount += $this->priceCurrency->round($value * $taxRate / 100);
                                }
                            } else {
                                $taxAmount = $this->priceCurrency->round(
                                    $value * $currentPercent / 100
                                );
                            }
                            $taxAmount = $this->priceCurrency->round($value * $currentPercent / 100);
                        }
                    }

                    $one = new \Magento\Framework\Object();
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
}
