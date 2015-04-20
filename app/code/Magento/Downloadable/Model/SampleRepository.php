<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Downloadable\Api\Data\File\ContentUploaderInterface;
use Magento\Downloadable\Api\Data\SampleInterface;
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
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $downloadableType;

    /**
     * @var ContentValidator
     */
    protected $contentValidator;

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
     * @param \Magento\Downloadable\Model\Product\Type $downloadableType
     * @param ContentValidator $contentValidator
     * @param ContentUploaderInterface $fileContentUploader
     * @param EncoderInterface $jsonEncoder
     * @param SampleFactory $sampleFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\Product\Type $downloadableType,
        ContentValidator $contentValidator,
        ContentUploaderInterface $fileContentUploader,
        EncoderInterface $jsonEncoder,
        SampleFactory $sampleFactory
    ) {
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->contentValidator = $contentValidator;
        $this->fileContentUploader = $fileContentUploader;
        $this->jsonEncoder = $jsonEncoder;
        $this->sampleFactory = $sampleFactory;
    }

    /**
     * Update downloadable sample of the given product
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Api\Data\SampleInterface $sample
     * @param bool $isGlobalScopeContent
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save(
        $productSku,
        SampleInterface $sample,
        $isGlobalScopeContent = false
    ) {
        $product = $this->productRepository->get($productSku, true);

        $sampleId = $sample->getId();
        if ($sampleId) {
            return $this->updateSample($product, $sample, $isGlobalScopeContent);
        } else {
            if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
                throw new InputException(__('Product type of the product must be \'downloadable\'.'));
            }
            if (!$this->contentValidator->isValid($sample)) {
                throw new InputException(__('Provided sample information is invalid.'));
            }

            if (!in_array($sample->getSampleType(), ['url', 'file'])) {
                throw new InputException(__('Invalid sample type.'));
            }

            $title = $sample->getTitle();
            if (empty($title)) {
                throw new InputException(__('Sample title cannot be empty.'));
            }

            return $this->saveSample($product, $sample, $isGlobalScopeContent);
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param SampleInterface $sample
     * @param bool $isGlobalScopeContent
     * @return int
     */
    protected function saveSample(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        SampleInterface $sample,
        $isGlobalScopeContent
    ) {
        $sampleData = [
            'sample_id' => $sample->getid() === null ? 0 : $sample->getid(),
            'is_delete' => 0,
            'type' => $sample->getSampleType(),
            'sort_order' => $sample->getSortOrder(),
            'title' => $sample->getTitle(),
        ];

        if ($sample->getSampleType() == 'file' && $sample->getSampleFile() === null) {
            $sampleData['file'] = $this->jsonEncoder->encode(
                [
                    $this->fileContentUploader->upload($sample->getSampleFileContent(), 'sample'),
                ]
            );
        } elseif ($sample->getSampleType() === 'url') {
            $sampleData['sample_url'] = $sample->getSampleUrl();
        } else {
            $sampleData['sample_file'] = $sample->getSampleFile();
        }

        $downloadableData = ['sample' => [$sampleData]];
        $product->setDownloadableData($downloadableData);
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }
        $this->downloadableType->save($product);
        return $product->getLastAddedSampleId();
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param SampleInterface $sample
     * @param bool $isGlobalScopeContent
     * @return int
     * @throws InputException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateSample(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        SampleInterface $sample,
        $isGlobalScopeContent
    ) {
        $sampleId = $sample->getId();
        /** @var $existingSample \Magento\Downloadable\Model\Sample */
        $existingSample = $this->sampleFactory->create()->load($sampleId);

        if (!$existingSample->getId()) {
            throw new NoSuchEntityException(__('There is no downloadable sample with provided ID.'));
        }

        if ($existingSample->getProductId() != $product->getId()) {
            throw new InputException(__('Provided downloadable sample is not related to given product.'));
        }

        $validateFileContent = $sample->getSampleFileContent() === null ? false : true;
        if (!$this->contentValidator->isValid($sample, $validateFileContent)) {
            throw new InputException(__('Provided sample information is invalid.'));
        }
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }

        $title = $sample->getTitle();
        if (empty($title)) {
            if ($isGlobalScopeContent) {
                throw new InputException(__('Sample title cannot be empty.'));
            }
            // use title from GLOBAL scope
            $existingSample->setTitle(null);
        } else {
            $existingSample->setTitle($sample->getTitle());
        }

        if ($sample->getSampleType() === 'file' && $sample->getSampleFileContent() === null) {
            $sample->setSampleFile($existingSample->getSampleFile());
        }
        $this->saveSample($product, $sample, $isGlobalScopeContent);
        return $existingSample->getId();
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
