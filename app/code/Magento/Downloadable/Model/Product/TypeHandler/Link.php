<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\ComponentInterface;

/**
 * Class Link
 */
class Link extends AbstractTypeHandler
{
    /**
     * @var array
     */
    private $sampleItem = [];

    /**
     * @var \Magento\Downloadable\Model\ComponentInterfaceFactory
     */
    private $linkFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link
     */
    private $linkResource;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link $linkResource
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Downloadable\Model\ResourceModel\Link $linkResource
    ) {
        parent::__construct($jsonHelper, $downloadableFile);
        $this->linkFactory = $linkFactory;
        $this->linkResource = $linkResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataKey()
    {
        return 'link';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierKey()
    {
        return 'link_id';
    }

    /**
     * {@inheritdoc}
     */
    public function save(Product $product, array $data)
    {
        parent::save($product, $data);
        if ($product->getLinksPurchasedSeparately()) {
            $product->setIsCustomOptionChanged();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function clear()
    {
        $this->sampleItem = [];
        return parent::clear();
    }

    /**
     * @return ComponentInterface
     */
    protected function createItem()
    {
        return $this->linkFactory->create();
    }

    /**
     * @param ComponentInterface $model
     * @param array $data
     * @param Product $product
     * @return void
     */
    protected function setDataToModel(ComponentInterface $model, array $data, Product $product)
    {
        $model->setData(
            $data
        )->setLinkType(
            $data['type']
        )->setProductId(
            $product->getData(
                $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
            )
        )->setStoreId(
            $product->getStoreId()
        )->setWebsiteId(
            $product->getStore()->getWebsiteId()
        )->setProductWebsiteIds(
            $product->getWebsiteIds()
        );
        if (null === $model->getPrice()) {
            $model->setPrice(0);
        }
        if ($model->getIsUnlimited()) {
            $model->setNumberOfDownloads(0);
        }
    }

    /**
     * @param ComponentInterface $model
     * @param Product $product
     * @return void
     */
    protected function linkToProduct(ComponentInterface $model, Product $product)
    {
        $product->setLastAddedLinkId($model->getId());
    }

    /**
     * @return void
     */
    protected function processDelete()
    {
        if ($this->deletedItems) {
            $this->linkResource->deleteItems($this->deletedItems);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function saveItem(Product $product, array $item)
    {
        if (isset($item['sample'])) {
            $this->sampleItem = $item['sample'];
            unset($item['sample']);
        }
        return parent::saveItem($product, $item);
    }

    /**
     * @param ComponentInterface $model
     * @param array $files
     * @return void
     */
    protected function setFiles(ComponentInterface $model, array $files)
    {
        $sampleFile = [];
        if ($this->sampleItem && isset($this->sampleItem['type'])) {
            if ($this->sampleItem['type'] == 'url' && $this->sampleItem['url'] != '') {
                $model->setSampleUrl($this->sampleItem['url']);
            }
            $model->setSampleType($this->sampleItem['type']);
            if (isset($this->sampleItem['file']) && $this->sampleItem['file']) {
                $sampleFile = $this->jsonHelper->jsonDecode($this->sampleItem['file']);
            }
        }
        if ($model->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
            $linkFileName = $this->downloadableFile->moveFileFromTmp(
                $this->createItem()->getBaseTmpPath(),
                $this->createItem()->getBasePath(),
                $files
            );
            $model->setLinkFile($linkFileName);
        }
        if ($model->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
            $linkSampleFileName = $this->downloadableFile->moveFileFromTmp(
                $this->createItem()->getBaseSampleTmpPath(),
                $this->createItem()->getBaseSamplePath(),
                $sampleFile
            );
            $model->setSampleFile($linkSampleFileName);
        }
    }
}
