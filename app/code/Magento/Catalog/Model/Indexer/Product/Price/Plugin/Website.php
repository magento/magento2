<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Price\Plugin\Website
 *
 * @since 2.0.0
 */
class Website
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     * @since 2.0.0
     */
    protected $_processor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterDelete(\Magento\Store\Model\ResourceModel\Website $subject, $result)
    {
        $this->_processor->markIndexerAsInvalid();
        return $result;
    }
}
