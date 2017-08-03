<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Product\TypeHandler;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\ComponentInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ObjectManager;

/**
 * Class AbstractTypeHandler
 * @since 2.0.0
 */
abstract class AbstractTypeHandler
{
    const FIELD_IS_DELETE = 'is_delete';

    const FIELD_FILE = 'file';

    /**
     * @var array
     * @since 2.0.0
     */
    protected $deletedItems = [];

    /**
     * @var Data
     * @since 2.0.0
     */
    protected $jsonHelper;

    /**
     * @var File
     * @since 2.0.0
     */
    protected $downloadableFile;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @param Data $jsonHelper
     * @param File $downloadableFile
     * @since 2.0.0
     */
    public function __construct(
        Data $jsonHelper,
        File $downloadableFile
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->downloadableFile = $downloadableFile;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    abstract public function getDataKey();

    /**
     * @return string
     * @since 2.0.0
     */
    abstract public function getIdentifierKey();

    /**
     * @param Product $product
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function save(Product $product, array $data)
    {
        $this->clear();
        if (isset($data[$this->getDataKey()])) {
            foreach ($data[$this->getDataKey()] as $item) {
                if ($this->isDelete($item)) {
                    $this->addToDeleteQueue($item);
                } else {
                    $this->saveItem($product, $item);
                }
            }
            $this->processDelete();
        }
        return $this;
    }

    /**
     * @return ComponentInterface
     * @since 2.0.0
     */
    abstract protected function createItem();

    /**
     * @param ComponentInterface $model
     * @param array $data
     * @param Product $product
     * @return void
     * @since 2.0.0
     */
    abstract protected function setDataToModel(
        ComponentInterface $model,
        array $data,
        Product $product
    );

    /**
     * @param ComponentInterface $model
     * @param Product $product
     * @return void
     * @since 2.0.0
     */
    abstract protected function linkToProduct(ComponentInterface $model, Product $product);

    /**
     * @param array $item
     * @return array
     * @since 2.0.0
     */
    protected function prepareItem(array $item)
    {
        unset($item[self::FIELD_IS_DELETE], $item[self::FIELD_FILE]);
        if (isset($item[$this->getIdentifierKey()]) && !$item[$this->getIdentifierKey()]) {
            unset($item[$this->getIdentifierKey()]);
        }
        return $item;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    abstract protected function processDelete();

    /**
     * @param array $item
     * @return bool
     * @since 2.0.0
     */
    protected function isDelete(array $item)
    {
        return isset($item[self::FIELD_IS_DELETE]) && '1' == $item[self::FIELD_IS_DELETE];
    }

    /**
     * @param array $item
     * @return array
     * @since 2.0.0
     */
    protected function getFiles(array $item)
    {
        $files = [];
        if (isset($item[self::FIELD_FILE]) && $item[self::FIELD_FILE]) {
            $files = $this->jsonHelper->jsonDecode($item[self::FIELD_FILE]);
        }
        return $files;
    }

    /**
     * @param ComponentInterface $model
     * @param array $files
     * @return void
     * @since 2.0.0
     */
    abstract protected function setFiles(ComponentInterface $model, array $files);

    /**
     * @param Product $product
     * @param array $item
     * @return array
     * @since 2.0.0
     */
    protected function saveItem(Product $product, array $item)
    {
        $files = $this->getFiles($item);
        $item = $this->prepareItem($item);

        $model = $this->createItem();
        $this->setDataToModel($model, $item, $product);
        $this->setFiles($model, $files);
        $model->save();
        $this->linkToProduct($model, $product);
        return $item;
    }

    /**
     * @param array $item
     * @return void
     * @since 2.0.0
     */
    protected function addToDeleteQueue(array $item)
    {
        if (!empty($item[$this->getIdentifierKey()])) {
            $this->deletedItems[] = $item[$this->getIdentifierKey()];
        }
    }

    /**
     * Clear type state
     *
     * @return void
     * @since 2.0.0
     */
    protected function clear()
    {
        $this->deletedItems = [];
    }

    /**
     * Get MetadataPool instance
     * @return MetadataPool
     * @since 2.1.0
     */
    protected function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
