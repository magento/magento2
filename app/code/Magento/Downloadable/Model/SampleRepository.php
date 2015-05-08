<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Downloadable\Api\Data\File\ContentUploaderInterface;
use Magento\Downloadable\Api\Data\SampleContentInterface;
use Magento\Downloadable\Model\Sample\ContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;

class SampleRepository implements \Magento\Downloadable\Api\SampleRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ContentValidator
     */
    protected $contentValidator;

    /**
     * @var Type
     */
    protected $downloadableType;

    /**
     * @var SampleInterfaceFactory
     */
    protected $sampleDataObjectFactory;

    /**
     * @var ContentUploaderInterface
     */
    protected $fileContentUploader;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Type $downloadableType
     * @param LinkInterfaceFactory $linkDataObjectFactory
     * @param SampleInterfaceFactory $sampleDataObjectFactory
     * @param ContentValidator $contentValidator
     * @param ContentUploaderInterface $fileContentUploader
     * @param EncoderInterface $jsonEncoder
     * @param SampleFactory $sampleFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Type $downloadableType,
        SampleInterfaceFactory $sampleDataObjectFactory,
        ContentValidator $contentValidator,
        ContentUploaderInterface $fileContentUploader,
        EncoderInterface $jsonEncoder,
        SampleFactory $sampleFactory
    ) {
        $this->productRepository = $productRepository;
        $this->contentValidator = $contentValidator;
        $this->fileContentUploader = $fileContentUploader;
        $this->jsonEncoder = $jsonEncoder;
        $this->sampleFactory = $sampleFactory;
        $this->downloadableType = $downloadableType;
        $this->sampleDataObjectFactory = $sampleDataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        $sampleList = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        $samples = $this->downloadableType->getSamples($product);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        foreach ($samples as $sample) {
            $sampleList[] = $this->buildSample($sample);
        }
        return $sampleList;
    }

    /**
     * Build a sample data object
     *
     * @param \Magento\Downloadable\Model\Sample $resourceData
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function buildSample($resourceData)
    {
        $sample = $this->sampleDataObjectFactory->create();
        $this->setBasicFields($resourceData, $sample);
        return $sample;
    }

    /**
     * Subroutine for buildLink and buildSample
     *
     * @param \Magento\Downloadable\Model\Link|\Magento\Downloadable\Model\Sample $resourceData
     * @param \Magento\Downloadable\Api\Data\LinkInterface|\Magento\Downloadable\Api\Data\SampleInterface $dataObject
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
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save(
        $productSku,
        SampleContentInterface $sampleContent,
        $sampleId = null,
        $isGlobalScopeContent = false
    ) {
        $product = $this->productRepository->get($productSku, true);

        if ($sampleId) {

            /** @var $sample \Magento\Downloadable\Model\Sample */
            $sample = $this->sampleFactory->create()->load($sampleId);

            if (!$sample->getId()) {
                throw new NoSuchEntityException(__('There is no downloadable sample with provided ID.'));
            }

            if ($sample->getProductId() != $product->getId()) {
                throw new InputException(__('Provided downloadable sample is not related to given product.'));
            }
            if (!$this->contentValidator->isValid($sampleContent)) {
                throw new InputException(__('Provided sample information is invalid.'));
            }
            if ($isGlobalScopeContent) {
                $product->setStoreId(0);
            }

            $title = $sampleContent->getTitle();
            if (empty($title)) {
                if ($isGlobalScopeContent) {
                    throw new InputException(__('Sample title cannot be empty.'));
                }
                // use title from GLOBAL scope
                $sample->setTitle(null);
            } else {
                $sample->setTitle($sampleContent->getTitle());
            }

            $sample->setProductId($product->getId())
                ->setStoreId($product->getStoreId())
                ->setSortOrder($sampleContent->getSortOrder())
                ->save();

            return $sample->getId();
        } else {

            if ($product->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
                throw new InputException(__('Product type of the product must be \'downloadable\'.'));
            }
            if (!$this->contentValidator->isValid($sampleContent)) {
                throw new InputException(__('Provided sample information is invalid.'));
            }

            if (!in_array($sampleContent->getSampleType(), ['url', 'file'])) {
                throw new InputException(__('Invalid sample type.'));
            }

            $title = $sampleContent->getTitle();
            if (empty($title)) {
                throw new InputException(__('Sample title cannot be empty.'));
            }

            $sampleData = [
                'sample_id' => 0,
                'is_delete' => 0,
                'type' => $sampleContent->getSampleType(),
                'sort_order' => $sampleContent->getSortOrder(),
                'title' => $sampleContent->getTitle(),
            ];

            if ($sampleContent->getSampleType() == 'file') {
                $sampleData['file'] = $this->jsonEncoder->encode(
                    [
                        $this->fileContentUploader->upload($sampleContent->getSampleFile(), 'sample'),
                    ]
                );
            } else {
                $sampleData['sample_url'] = $sampleContent->getSampleUrl();
            }

            $downloadableData = ['sample' => [$sampleData]];
            $product->setDownloadableData($downloadableData);
            if ($isGlobalScopeContent) {
                $product->setStoreId(0);
            }
            $product->save();
            return $product->getLastAddedSampleId();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($sampleId)
    {
        /** @var $sample \Magento\Downloadable\Model\Sample */
        $sample = $this->sampleFactory->create()->load($sampleId);
        if (!$sample->getId()) {
            throw new NoSuchEntityException(__('There is no downloadable sample with provided ID.'));
        }
        $sample->delete();
        return true;
    }
}
