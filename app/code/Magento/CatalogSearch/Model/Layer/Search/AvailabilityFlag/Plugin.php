<?php
/**
 *
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
namespace Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag;

use Magento\CatalogSearch\Model\Resource\EngineProvider;

class Plugin
{
    const XML_PATH_DISPLAY_LAYER_COUNT = 'catalog/search/use_layered_navigation_count';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\EngineProvider
     */
    protected $engineProvider;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param EngineProvider $engineProvider
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        EngineProvider $engineProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->engineProvider = $engineProvider;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Layer $layer
     * @param array $filters
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsEnabled(
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $subject,
        \Closure $proceed,
        $layer,
        $filters
    ) {
        $_isLNAllowedByEngine = $this->engineProvider->get()->isLayeredNavigationAllowed();
        if (!$_isLNAllowedByEngine) {
            return false;
        }
        $availableResCount = (int)$this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY_LAYER_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$availableResCount || ($availableResCount > $layer->getProductCollection()->getSize())) {
            return $proceed($layer, $filters);
        }
        return false;
    }
}
