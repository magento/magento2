<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;

/**
 * Class \Magento\Msrp\Model\Config
 *
 */
class Config
{
    /**#@+
     * Minimum advertise price constants
     */
    const XML_PATH_MSRP_ENABLED = 'sales/msrp/enabled';
    const XML_PATH_MSRP_DISPLAY_ACTUAL_PRICE_TYPE = 'sales/msrp/display_price_type';
    const XML_PATH_MSRP_EXPLANATION_MESSAGE = 'sales/msrp/explanation_message';
    const XML_PATH_MSRP_EXPLANATION_MESSAGE_WHATS_THIS = 'sales/msrp/explanation_message_whats_this';
    /**#@-*/

    /**#@-*/
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Escaper $escaper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->storeId = $store;
        return $this;
    }

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_MSRP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Return Msrp display actual type
     *
     * @return null|string
     */
    public function getDisplayActualPriceType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MSRP_DISPLAY_ACTUAL_PRICE_TYPE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Return Msrp explanation message
     *
     * @return string
     */
    public function getExplanationMessage()
    {
        return $this->escaper->escapeHtml(
            $this->scopeConfig->getValue(
                self::XML_PATH_MSRP_EXPLANATION_MESSAGE,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            ),
            ['b', 'br', 'strong', 'i', 'u', 'p', 'span']
        );
    }

    /**
     * Return Msrp explanation message for "Whats This" window
     *
     * @return string
     */
    public function getExplanationMessageWhatsThis()
    {
        return $this->escaper->escapeHtml(
            $this->scopeConfig->getValue(
                self::XML_PATH_MSRP_EXPLANATION_MESSAGE_WHATS_THIS,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            ),
            ['b', 'br', 'strong', 'i', 'u', 'p', 'span']
        );
    }
}
