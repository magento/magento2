<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder;

/**
 * Class Row reindex action
 */
class Row extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer
     */
    protected $flatItemWriter;

    /**
     * @var Eraser
     */
    protected $flatItemEraser;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @param Indexer $flatItemWriter
     * @param Eraser $flatItemEraser
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder,
        Indexer $flatItemWriter,
        Eraser $flatItemEraser
    ) {
        parent::__construct(
            $resource,
            $storeManager,
            $productHelper,
            $productType,
            $tableBuilder,
            $flatTableBuilder
        );
        $this->flatItemWriter = $flatItemWriter;
        $this->flatItemEraser = $flatItemEraser;
    }

    /**
     * Execute row reindex action
     *
     * @param int|null $id
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     * @throws \Magento\Framework\Model\Exception
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Framework\Model\Exception(__('Could not rebuild index for undefined product'));
        }
        $ids = [$id];
        foreach ($this->_storeManager->getStores() as $store) {
            $tableExists = $this->_isFlatTableExists($store->getId());
            if ($tableExists) {
                $this->flatItemEraser->removeDeletedProducts($ids, $store->getId());
            }
            if (isset($ids[0])) {
                if (!$tableExists) {
                    $this->_flatTableBuilder->build(
                        $store->getId(),
                        [$ids[0]],
                        $this->_valueFieldSuffix,
                        $this->_tableDropSuffix,
                        false
                    );
                }
                $this->flatItemWriter->write($store->getId(), $ids[0], $this->_valueFieldSuffix);
            }
        }
        return $this;
    }
}
