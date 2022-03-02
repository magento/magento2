<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\ViewModel;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AdminFormVisibilityViewModel implements ArgumentInterface
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConfig $imsConfig
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
