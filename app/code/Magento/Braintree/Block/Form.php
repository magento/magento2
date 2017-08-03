<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\Braintree\Gateway\Config\Config as GatewayConfig;
use Magento\Braintree\Model\Adminhtml\Source\CcType;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class Form
 * @since 2.0.0
 */
class Form extends Cc
{

    /**
     * @var Quote
     * @since 2.0.0
     */
    protected $sessionQuote;

    /**
     * @var Config
     * @since 2.1.0
     */
    protected $gatewayConfig;

    /**
     * @var CcType
     * @since 2.1.0
     */
    protected $ccType;

    /**
     * @var Data
     * @since 2.1.0
     */
    private $paymentDataHelper;

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Quote $sessionQuote
     * @param GatewayConfig $gatewayConfig
     * @param CcType $ccType
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        CcType $ccType,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->ccType = $ccType;
    }

    /**
     * Get list of available card types of order billing address country
     * @return array
     * @since 2.0.0
     */
    public function getCcAvailableTypes()
    {
        $configuredCardTypes = $this->getConfiguredCardTypes();
        $countryId = $this->sessionQuote->getQuote()->getBillingAddress()->getCountryId();
        return $this->filterCardTypesForCountry($configuredCardTypes, $countryId);
    }

    /**
     * Check if cvv validation is available
     * @return boolean
     * @since 2.0.0
     */
    public function useCvv()
    {
        return $this->gatewayConfig->isCvvEnabled();
    }

    /**
     * Check if vault enabled
     * @return bool
     * @since 2.1.0
     */
    public function isVaultEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /**
     * Get card types available for Braintree
     * @return array
     * @since 2.1.0
     */
    private function getConfiguredCardTypes()
    {
        $types = $this->ccType->getCcTypeLabelMap();
        $configCardTypes = array_fill_keys($this->gatewayConfig->getAvailableCardTypes(), '');

        return array_intersect_key($types, $configCardTypes);
    }

    /**
     * Filter card types for specific country
     * @param array $configCardTypes
     * @param string $countryId
     * @return array
     * @since 2.1.0
     */
    private function filterCardTypesForCountry(array $configCardTypes, $countryId)
    {
        $filtered = $configCardTypes;
        $countryCardTypes = $this->gatewayConfig->getCountryAvailableCardTypes($countryId);
        // filter card types only if specific card types are set for country
        if (!empty($countryCardTypes)) {
            $availableTypes = array_fill_keys($countryCardTypes, '');
            $filtered = array_intersect_key($filtered, $availableTypes);
        }
        return $filtered;
    }

    /**
     * Get configured vault payment for Braintree
     * @return VaultPaymentInterface
     * @since 2.1.0
     */
    private function getVaultPayment()
    {
        return $this->getPaymentDataHelper()->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }

    /**
     * Get payment data helper instance
     * @return Data
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }
}
