<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * LoginAsCustomer module config model.
 */
class Config
{
    /**
     * Extension config path
     */
    private const XML_PATH_EXTENSION_ENABLED = 'loginascustomer/general/enabled';
    private const ENABLE_STORE_VIEW_MANUAL_CHOICE = 'loginascustomer/general/enable_store_view_manual_choice';

    public const TIME_FRAME = 60;

    /**
     * @var ScopeConfigInterface
     */

    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $metadata
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->metadata = $metadata;
    }

    /**
     * Check if Login As Customer extension is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_EXTENSION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Check if store view manual choice is enabled.
     *
     * @return bool
     */
    public function isManualChoiceEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::ENABLE_STORE_VIEW_MANUAL_CHOICE,
            ScopeInterface::SCOPE_STORE,
            null
        );
    }
}
