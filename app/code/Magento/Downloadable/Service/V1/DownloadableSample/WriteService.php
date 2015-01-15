<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Downloadable\Service\V1\Data\FileContentUploaderInterface;
use Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent;
use Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DownloadableSampleContentValidator
     */
    protected $contentValidator;

    /**
     * @var FileContentUploaderInterface
     */
    protected $fileContentUploader;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param DownloadableSampleContentValidator $contentValidator
     * @param FileContentUploaderInterface $fileContentUploader
     * @param EncoderInterface $jsonEncoder
     * @param SampleFactory $sampleFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DownloadableSampleContentValidator $contentValidator,
        FileContentUploaderInterface $fileContentUploader,
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
     */
    public function create($productSku, DownloadableSampleContent $sampleContent, $isGlobalScopeContent = false)
    {
        $product = $this->productRepository->get($productSku, true);
        if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            throw new InputException('Product type of the product must be \'downloadable\'.');
        }
        if (!$this->contentValidator->isValid($sampleContent)) {
            throw new InputException('Provided sample information is invalid.');
        }

        if (!in_array($sampleContent->getSampleType(), ['url', 'file'])) {
            throw new InputException('Invalid sample type.');
        }

        $title = $sampleContent->getTitle();
        if (empty($title)) {
            throw new InputException('Sample title cannot be empty.');
        }

        $sampleData = [
            'sample_id' => 0,
            'is_delete' => 0,
            'type' => $sampleContent->getSampleType(),
            'sort_order' => $sampleContent->getSortOrder(),
            'title' => $sampleContent->getTitle(),
        ];

        if ($sampleContent->getSampleType() == 'file') {
            $sampleData['file'] = $this->jsonEncoder->encode([
                $this->fileContentUploader->upload($sampleContent->getSampleFile(), 'sample'),
            ]);
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

    /**
     * {@inheritdoc}
     */
    public function update(
        $productSku,
        $sampleId,
        DownloadableSampleContent $sampleContent,
        $isGlobalScopeContent = false
    ) {
        $product = $this->productRepository->get($productSku, true);
        /** @var $sample \Magento\Downloadable\Model\Sample */
        $sample = $this->sampleFactory->create()->load($sampleId);
        if (!$sample->getId()) {
            throw new NoSuchEntityException('There is no downloadable sample with provided ID.');
        }
        if ($sample->getProductId() != $product->getId()) {
            throw new InputException('Provided downloadable sample is not related to given product.');
        }
        if (!$this->contentValidator->isValid($sampleContent)) {
            throw new InputException('Provided sample information is invalid.');
        }
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }

        $title = $sampleContent->getTitle();
        if (empty($title)) {
            if ($isGlobalScopeContent) {
                throw new InputException('Sample title cannot be empty.');
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

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($sampleId)
    {
        /** @var $sample \Magento\Downloadable\Model\Sample */
        $sample = $this->sampleFactory->create()->load($sampleId);
        if (!$sample->getId()) {
            throw new NoSuchEntityException('There is no downloadable sample with provided ID.');
        }
        $sample->delete();
        return true;
    }
}
