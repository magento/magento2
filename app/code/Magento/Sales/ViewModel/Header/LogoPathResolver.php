<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Header;

use Magento\Config\Model\Config\Backend\Image\Logo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\ViewModel\Block\Html\Header\LogoPathResolverInterface;

/**
 * Class for resolving logo path
 */
class LogoPathResolver implements LogoPathResolverInterface, ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $registry;
    }

    /**
     * Return logo image path
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        $storeId = null;
        $order = $this->coreRegistry->registry('current_order');
        if ($order instanceof Order) {
            $storeId = $order->getStoreId();
        }
        $salesLogoPath = $this->scopeConfig->getValue(
            'sales/identity/logo_html',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($salesLogoPath !== null) {
            return 'sales/store/logo_html/' . $salesLogoPath;
        }

        $headerLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($headerLogoPath !== null) {
            return Logo::UPLOAD_DIR . '/' . $headerLogoPath;
        }

        return null;
    }
}
