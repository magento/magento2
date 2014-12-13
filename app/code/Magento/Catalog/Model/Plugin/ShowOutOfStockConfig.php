<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Plugin;

class ShowOutOfStockConfig
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_eavIndexerProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavIndexerProcessor
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
     */
    public function afterSave(\Magento\Framework\App\Config\Value $subject, $result)
    {
        if ($subject->isValueChanged()) {
            $this->_eavIndexerProcessor->markIndexerAsInvalid();
        }
        return $result;
    }
}
