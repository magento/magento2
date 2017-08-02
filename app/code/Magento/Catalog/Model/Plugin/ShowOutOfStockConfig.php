<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin;

/**
 * Class \Magento\Catalog\Model\Plugin\ShowOutOfStockConfig
 *
 * @since 2.0.0
 */
class ShowOutOfStockConfig
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     * @since 2.0.0
     */
    protected $_eavIndexerProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavIndexerProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavIndexerProcessor)
    {
        $this->_eavIndexerProcessor = $eavIndexerProcessor;
    }

    /**
     * After save handler
     *
     * @param \Magento\Framework\App\Config\Value $subject
     * @param mixed $result
     *
     * @return mixed
     * @since 2.0.0
     */
    public function afterSave(\Magento\Framework\App\Config\Value $subject, $result)
    {
        if ($subject->isValueChanged()) {
            $this->_eavIndexerProcessor->markIndexerAsInvalid();
        }
        return $result;
    }
}
