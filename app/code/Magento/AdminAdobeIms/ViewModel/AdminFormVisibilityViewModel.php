<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class AdminFormVisibilityViewModel implements ArgumentInterface
{

    private \Magento\AdminAdobeIms\Service\ImsConfig $imsConfig;

    public function __construct(
        \Magento\AdminAdobeIms\Service\ImsConfig $imsConfig
    ) {
        $this->imsConfig = $imsConfig;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return !$this->imsConfig->enabled();
    }
}
