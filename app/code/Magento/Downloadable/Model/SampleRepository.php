<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @var ContentUploaderInterface
     */
    protected $fileContentUploader;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ContentValidator $contentValidator
     * @param ContentUploaderInterface $fileContentUploader
     * @param EncoderInterface $jsonEncoder
     * @param SampleFactory $sampleFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
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

            if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
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
