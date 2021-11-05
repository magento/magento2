<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\ViewModel\Block\Html\Header;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Logo size resolver view model
 */
class LogoSizeResolver implements LogoSizeResolverInterface, ArgumentInterface
{
    /**
     * Logo width config path
     */
    private const XML_PATH_DESIGN_HEADER_LOGO_WIDTH = 'design/header/logo_width';

    /**
     * Logo height config path
     */
    private const XML_PATH_DESIGN_HEADER_LOGO_HEIGHT = 'design/header/logo_height';

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
     * @inheritdoc
     */
    public function getWidth(?int $storeId = null): ?int
    {
        return $this->getConfig(self::XML_PATH_DESIGN_HEADER_LOGO_WIDTH, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getHeight(?int $storeId = null): ?int
    {
        return $this->getConfig(self::XML_PATH_DESIGN_HEADER_LOGO_HEIGHT, $storeId);
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param int|null $storeId
     * @return int|null
     */
    private function getConfig(string $path, ?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value === null ? null : (int) $value;
    }
}
