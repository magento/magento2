<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional;

/**
 * Backend grid widget massaction item additional action interface
 *
 * @api
 */
interface AdditionalInterface
{
    /**
     * Create additional action from configuration
     *
     * @param array $configuration
     * @return $this
     */
    public function createFromConfiguration(array $configuration);
}
