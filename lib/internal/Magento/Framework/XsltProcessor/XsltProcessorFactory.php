<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
