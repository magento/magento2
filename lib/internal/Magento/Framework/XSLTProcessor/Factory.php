<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\XSLTProcessor;

/**
 * XSLTProcessor document factory
 */
class Factory
{
    /**
     * Create empty XSLTProcessor instance.
     *
     * @return \XSLTProcessor
     */
    public function createXSLTProcessor()
    {
        return new \XSLTProcessor();
    }

}
