<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml;

/**
 * @api
 */
interface EntityCodeResolverInterface
{
    /**
     * Returns loaded Code model object
     *
     * @return \Magento\GoogleOptimizer\Model\Code
     */
    public function getCode();
}
