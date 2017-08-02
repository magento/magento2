<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\ComponentInterface;

/**
 * Class Sample
 * @api
 * @since 2.0.0
 */
class Sample extends AbstractTypeHandler
{
    const DATA_KEY = 'sample';
    const IDENTIFIER_KEY = 'sample_id';

    /**
     * @var \Magento\Downloadable\Model\SampleFactory
     * @since 2.0.0
     */
    private $sampleFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\SampleFactory
     * @since 2.0.0
     */
    private $sampleResourceFactory;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Downloadable\Model\SampleFactory $sampleFactory
     * @param \Magento\Downloadable\Model\ResourceModel\SampleFactory $sampleResourceFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Downloadable\Model\SampleFactory $sampleFactory,
        \Magento\Downloadable\Model\ResourceModel\SampleFactory $sampleResourceFactory
    ) {
        parent::__construct($jsonHelper, $downloadableFile);
        $this->sampleFactory = $sampleFactory;
        $this->sampleResourceFactory = $sampleResourceFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDataKey()
    {
        return self::DATA_KEY;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIdentifierKey()
    {
        return self::IDENTIFIER_KEY;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function processDelete()
    {
        if ($this->deletedItems) {
            $this->sampleResourceFactory->create()->deleteItems($this->deletedItems);
        }
    }

    /**
     * @return ComponentInterface
     * @since 2.0.0
     */
    protected function createItem()
    {
        return $this->sampleFactory->create();
    }

    /**
     * @param ComponentInterface $model
     * @param array $data
     * @param Product $product
     * @return void
     * @since 2.0.0
     */
    protected function setDataToModel(ComponentInterface $model, array $data, Product $product)
    {
        $model->setData(
            $data
        )->setSampleType(
            $data['type']
        )->setProductId(
            $product->getData(
                $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
            )
        );
        $model->setStoreId(
            $product->getStoreId()
        );
    }

    /**
     * @param ComponentInterface $model
     * @param array $files
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function setFiles(ComponentInterface $model, array $files)
    {
        if ($model->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
            $fileName = $this->downloadableFile->moveFileFromTmp(
                $model->getBaseTmpPath(),
                $model->getBasePath(),
                $files
            );
            $model->setSampleFile($fileName);
        }
        return $this;
    }

    /**
     * @param ComponentInterface $model
     * @param Product $product
     * @return void
     * @since 2.0.0
     */
    protected function linkToProduct(ComponentInterface $model, Product $product)
    {
        $product->setLastAddedSampleId($model->getId());
        return $this;
    }
}
