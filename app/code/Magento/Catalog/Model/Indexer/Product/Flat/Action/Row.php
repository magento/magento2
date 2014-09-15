<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder;
use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;

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
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @param Indexer $flatItemWriter
     * @param Eraser $flatItemEraser
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
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
        $ids = array($id);
        foreach ($this->_storeManager->getStores() as $store) {
            $tableExists = $this->_isFlatTableExists($store->getId());
            if ($tableExists) {
                $this->flatItemEraser->removeDeletedProducts($ids, $store->getId());
            }
            if (isset($ids[0])) {
                if (!$tableExists) {
                    $this->_flatTableBuilder->build(
                        $store->getId(),
                        array($ids[0]),
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
