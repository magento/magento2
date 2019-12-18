<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);
namespace Magento\Swatches\ViewModel\Product\Renderer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Configurable
 */
class Configurable implements ArgumentInterface
{
    /**
     * Config path if swatch tooltips are enabled
     */
    private const XML_PATH_SHOW_SWATCH_TOOLTIP = 'catalog/frontend/show_swatch_tooltip';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Configurable constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get config if swatch tooltips should be rendered.
     *
     * @return string
     */
    public function getShowSwatchTooltip()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_SWATCH_TOOLTIP,
            ScopeInterface::SCOPE_STORE
        );
    }
}
