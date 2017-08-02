<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional;

/**
 * Backend grid widget massaction item additional action interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
interface AdditionalInterface
{
    /**
     * @param array $configuration
     * @return $this
     * @api
     * @since 2.0.0
     */
    public function createFromConfiguration(array $configuration);
}
