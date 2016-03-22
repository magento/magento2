<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional;

/**
 * Backend grid widget massaction item additional action interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface AdditionalInterface
{
    /**
     * @param array $configuration
     * @return $this
     * @api
     */
    public function createFromConfiguration(array $configuration);
}
