<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\XsltProcessor;

/**
 * XSLTProcessor document factory
 * @since 2.0.0
 */
class XsltProcessorFactory
{
    /**
     * Create empty XSLTProcessor instance.
     *
     * @return \XSLTProcessor
     * @since 2.0.0
     */
    public function create()
    {
        return new \XSLTProcessor();
    }
}
