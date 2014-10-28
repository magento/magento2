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
namespace Magento\Msrp\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\StoreManagerInterface;
use Magento\Framework\Escaper;

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

    /**
     * @var ScopeConfigInterface
     */
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
            array('b', 'br', 'strong', 'i', 'u', 'p', 'span')
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
            array('b', 'br', 'strong', 'i', 'u', 'p', 'span')
        );
    }
}
