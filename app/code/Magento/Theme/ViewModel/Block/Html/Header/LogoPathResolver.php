<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\ViewModel\Block\Html\Header;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\Config\Backend\Image\Logo;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

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
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return logo image path
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        $path = null;
        $scopeType = ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue('general/single_store_mode/enabled') === "1") {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
        }
        $storeLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            $scopeType
        );
        if ($storeLogoPath !== null) {
            $path = Logo::UPLOAD_DIR . '/' . $storeLogoPath;
        }
        return $path;
    }
}
