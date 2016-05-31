<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\XsltProcessor;

/**
 * XSLTProcessor document factory
 */
class XsltProcessorFactory
{
    /**
     * Create empty XSLTProcessor instance.
     *
     * @return \XSLTProcessor
     */
    public function create()
    {
        return new \XSLTProcessor();
    }
}
