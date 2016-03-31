<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

class Website
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_processor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Price\Processor $processor)
    {
        $this->_processor = $processor;
    }

    /**
     * Invalidate price indexer
     *
     * @param \Magento\Store\Model\ResourceModel\Website $subject
     * @param \Magento\Store\Model\ResourceModel\Website $result
     * @return \Magento\Store\Model\ResourceModel\Website
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(\Magento\Store\Model\ResourceModel\Website $subject, $result)
    {
        $this->_processor->markIndexerAsInvalid();
        return $result;
    }
}
