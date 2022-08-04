<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Extended UI component configuration provider for block instances
 *
 * @api
 */
interface ConfigProviderInterface extends ArgumentInterface
{
    /**
     * Get configuration array
     *
     * @return array
     */
    public function get(): array;
}
