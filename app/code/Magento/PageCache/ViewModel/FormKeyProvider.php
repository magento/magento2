<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\PageCache\Model\Config;

/**
 * Adds script to update form key from cookie after script rendering
 */
class FormKeyProvider implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Is full page cache enabled
     *
     * @return bool
     */
    public function isFullPageCacheEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
