<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Sample\ContentValidator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Downloadable\Model\ResourceModel\Sample as ResourceModel;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Downloadable\Helper\File;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class SampleRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleRepository implements \Magento\Downloadable\Api\SampleRepositoryInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ContentValidator
     */
    protected $contentValidator;

    /**
     * @var Type
     */
    protected $productType;

    /**
     * @var SampleInterfaceFactory
     */
    protected $sampleFactory;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var File
     */
    protected $downloadableFile;

    /**
     * @param MetadataPool $metadataPool
     * @param ProductRepositoryInterface $productRepository
     * @param Type $productType
     * @param ResourceModel $resourceModel
     * @param SampleInterfaceFactory $sampleFactory
     * @param ContentValidator $contentValidator
     * @param JsonHelper $jsonHelper
     * @param File $downloadableFile
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductRepositoryInterface $productRepository,
        Type $productType,
        ResourceModel $resourceModel,
        SampleInterfaceFactory $sampleFactory,
        ContentValidator $contentValidator,
        JsonHelper $jsonHelper,
        File $downloadableFile
    ) {
        $this->metadataPool = $metadataPool;
        $this->productRepository = $productRepository;
        $this->productType = $productType;
        $this->resourceModel = $resourceModel;
        $this->contentValidator = $contentValidator;
        $this->jsonHelper = $jsonHelper;
        $this->sampleFactory = $sampleFactory;
        $this->downloadableFile = $downloadableFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        return $this->getSamplesByProduct($product);
    }

    /**
     * Build a sample data object
     *
     * @param \Magento\Downloadable\Model\Sample $resourceData
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function buildSample($resourceData)
    {
        $sample = $this->sampleFactory->create();
        $this->setBasicFields($resourceData, $sample);
        return $sample;
    }

    /**
     * Subroutine for buildLink and buildSample
     *
     * @param Sample $resourceData
     * @param SampleInterface $dataObject
     * @return null
     */
    protected function setBasicFields($resourceData, $dataObject)
    {
        $dataObject->setId($resourceData->getId());
        $storeTitle = $resourceData->getStoreTitle();
        $title = $resourceData->getTitle();
        if (!empty($storeTitle)) {
            $dataObject->setTitle($storeTitle);
        } else {
            $dataObject->setTitle($title);
        }
        $dataObject->setSortOrder($resourceData->getSortOrder());
        $dataObject->setSampleType($resourceData->getSampleType());
        $dataObject->setSampleFile($resourceData->getSampleFile());
        $dataObject->setSampleUrl($resourceData->getSampleUrl());
    }

    /**
     * List of links with associated samples
     *
     * @param ProductInterface $product
     * @return SampleInterface[]
     */
    public function getSamplesByProduct(ProductInterface $product)
    {
        $sampleList = [];
        $samples = $this->productType->getSamples($product);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        foreach ($samples as $sample) {
            $sampleList[] = $this->buildSample($sample);
        }
        return $sampleList;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($sku, SampleInterface $sample, $isGlobalScopeContent = true)
    {
        $product = $this->productRepository->get($sku, true);
        if ($product->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
            throw new InputException(__('Product type of the product must be \'downloadable\'.'));
        }
        //if (!$this->contentValidator->isValid($sample)) {
        //    throw new InputException(__('Provided sample information is invalid.'));
        //}
        if (!in_array($sample->getSampleType(), ['url', 'file'])) {
            throw new InputException(__('Invalid sample type.'));
        }

        $title = $sample->getTitle();
        if (empty($title)) {
            throw new InputException(__('Sample title cannot be empty.'));
        }
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $sample->setProductId($product->getData($metadata->getLinkField()));
        $this->setFiles($sample);
        return $this->resourceModel->save($sample);
    }

    /**
     * Load file and set path to sample
     *
     * @param SampleInterface $sample
     * @return void
     */
    protected function setFiles(SampleInterface $sample)
    {
        if ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE && $sample->getFile()) {
            $sampleFileName = $this->downloadableFile->moveFileFromTmp(
                $sample->getBaseTmpPath(),
                $sample->getBasePath(),
                $this->jsonHelper->jsonDecode($sample->getFile())
            );
            $sample->setSampleFile($sampleFileName);
            $sample->setSampleUrl(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        /** @var $sample \Magento\Downloadable\Model\Sample */
        $sample = $this->sampleFactory->create()->load($id);
        if (!$sample->getId()) {
            throw new NoSuchEntityException(__('There is no downloadable sample with provided ID.'));
        }
        try {
            $this->resourceModel->delete($sample);
        } catch (\Exception $exception) {
            throw new StateException(__('Cannot delete sample with id %1', $sample->getId()), $exception);
        }
        return true;
    }
}
