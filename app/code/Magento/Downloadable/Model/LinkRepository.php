<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\Data\LinkContentInterface;
use Magento\Downloadable\Api\Data\File\ContentUploaderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;

class LinkRepository implements \Magento\Downloadable\Api\LinkRepositoryInterface
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
     * @var \Magento\Downloadable\Api\Data\LinkDataBuilder
     */
    protected $linkBuilder;

    /**
     * @var \Magento\Downloadable\Api\Data\SampleDataBuilder
     */
    protected $sampleBuilder;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\Downloadable\Model\Link\ContentValidator
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
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Downloadable\Model\Product\Type $downloadableType
     * @param \Magento\Downloadable\Api\Data\LinkDataBuilder $linkBuilder
     * @param \Magento\Downloadable\Api\Data\SampleDataBuilder $sampleBuilder
     * @param LinkFactory $linkFactory
     * @param Link\ContentValidator $contentValidator
     * @param EncoderInterface $jsonEncoder
     * @param ContentUploaderInterface $fileContentUploader
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\Product\Type $downloadableType,
        \Magento\Downloadable\Api\Data\LinkDataBuilder $linkBuilder,
        \Magento\Downloadable\Api\Data\SampleDataBuilder $sampleBuilder,
        LinkFactory $linkFactory,
        Link\ContentValidator $contentValidator,
        EncoderInterface $jsonEncoder,
        ContentUploaderInterface $fileContentUploader
    ) {
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->linkBuilder = $linkBuilder;
        $this->sampleBuilder = $sampleBuilder;
        $this->linkFactory = $linkFactory;
        $this->contentValidator = $contentValidator;
        $this->jsonEncoder = $jsonEncoder;
        $this->fileContentUploader = $fileContentUploader;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks($productSku)
    {
        $linkList = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);
        $links = $this->downloadableType->getLinks($product);
        /** @var \Magento\Downloadable\Model\Link $link */
        foreach ($links as $link) {
            $linkList[] = $this->buildLink($link);
        }
        return $linkList;
    }

    /**
     * Build a link data object
     *
     * @param \Magento\Downloadable\Model\Link $resourceData
     * @return \Magento\Downloadable\Model\Link
     */
    protected function buildLink($resourceData)
    {
        $this->setBasicFields($resourceData, $this->linkBuilder);
        $this->linkBuilder->setPrice($resourceData->getPrice());
        $this->linkBuilder->setNumberOfDownloads($resourceData->getNumberOfDownloads());
        $this->linkBuilder->setIsShareable($resourceData->getIsShareable());
        $this->linkBuilder->setLinkType($resourceData->getLinkType());
        $this->linkBuilder->setLinkFile($resourceData->getLinkFile());
        $this->linkBuilder->setLinkUrl($resourceData->getLinkUrl());

        return $this->linkBuilder->create();
    }

    /**
     * Subroutine for buildLink and buildSample
     *
     * @param \Magento\Downloadable\Model\Link|\Magento\Downloadable\Model\Sample $resourceData
     * @param \Magento\Downloadable\Api\Data\LinkDataBuilder|\Magento\Downloadable\Api\Data\SampleDataBuilder $builder
     * @return null
     */
    protected function setBasicFields($resourceData, $builder)
    {
        $builder->populateWithArray([]);
        $builder->setId($resourceData->getId());
        $storeTitle = $resourceData->getStoreTitle();
        $title = $resourceData->getTitle();
        if (!empty($storeTitle)) {
            $builder->setTitle($storeTitle);
        } else {
            $builder->setTitle($title);
        }
        $builder->setSortOrder($resourceData->getSortOrder());
        $builder->setSampleType($resourceData->getSampleType());
        $builder->setSampleFile($resourceData->getSampleFile());
        $builder->setSampleUrl($resourceData->getSampleUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function getSamples($productSku)
    {
        $sampleList = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);
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
        $this->setBasicFields($resourceData, $this->sampleBuilder);
        return $this->sampleBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    public function save($productSku, LinkContentInterface $linkContent, $linkId = null, $isGlobalScopeContent = false)
    {
        $product = $this->productRepository->get($productSku, true);
        if ($linkId) {

            /** @var $link \Magento\Downloadable\Model\Link */
            $link = $this->linkFactory->create()->load($linkId);
            if (!$link->getId()) {
                throw new NoSuchEntityException('There is no downloadable link with provided ID.');
            }
            if ($link->getProductId() != $product->getId()) {
                throw new InputException('Provided downloadable link is not related to given product.');
            }
            if (!$this->contentValidator->isValid($linkContent)) {
                throw new InputException('Provided link information is invalid.');
            }
            if ($isGlobalScopeContent) {
                $product->setStoreId(0);
            }
            $title = $linkContent->getTitle();
            if (empty($title)) {
                if ($isGlobalScopeContent) {
                    throw new InputException('Link title cannot be empty.');
                }
                // use title from GLOBAL scope
                $link->setTitle(null);
            } else {
                $link->setTitle($linkContent->getTitle());
            }

            $link->setProductId($product->getId())
                ->setStoreId($product->getStoreId())
                ->setWebsiteId($product->getStore()->getWebsiteId())
                ->setProductWebsiteIds($product->getWebsiteIds())
                ->setSortOrder($linkContent->getSortOrder())
                ->setPrice($linkContent->getPrice())
                ->setIsShareable($linkContent->isShareable())
                ->setNumberOfDownloads($linkContent->getNumberOfDownloads())
                ->save();
            return true;
        } else {
            $product = $this->productRepository->get($productSku, true);
            if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
                throw new InputException('Product type of the product must be \'downloadable\'.');
            }
            if (!$this->contentValidator->isValid($linkContent)) {
                throw new InputException('Provided link information is invalid.');
            }

            if (!in_array($linkContent->getLinkType(), ['url', 'file'])) {
                throw new InputException('Invalid link type.');
            }
            $title = $linkContent->getTitle();
            if (empty($title)) {
                throw new InputException('Link title cannot be empty.');
            }

            $linkData = [
                'link_id' => 0,
                'is_delete' => 0,
                'type' => $linkContent->getLinkType(),
                'sort_order' => $linkContent->getSortOrder(),
                'title' => $linkContent->getTitle(),
                'price' => $linkContent->getPrice(),
                'number_of_downloads' => $linkContent->getNumberOfDownloads(),
                'is_shareable' => $linkContent->isShareable(),
            ];

            if ($linkContent->getLinkType() == 'file') {
                $linkData['file'] = $this->jsonEncoder->encode(
                    [
                        $this->fileContentUploader->upload($linkContent->getLinkFile(), 'link_file'),
                    ]
                );
            } else {
                $linkData['link_url'] = $linkContent->getLinkUrl();
            }

            if ($linkContent->getSampleType() == 'file') {
                $linkData['sample']['type'] = 'file';
                $linkData['sample']['file'] = $this->jsonEncoder->encode(
                    [
                        $this->fileContentUploader->upload($linkContent->getSampleFile(), 'link_sample_file'),
                    ]
                );
            } elseif ($linkContent->getSampleType() == 'url') {
                $linkData['sample']['type'] = 'url';
                $linkData['sample']['url'] = $linkContent->getSampleUrl();
            }

            $downloadableData = ['link' => [$linkData]];
            $product->setDownloadableData($downloadableData);
            if ($isGlobalScopeContent) {
                $product->setStoreId(0);
            }
            $product->save();
            return $product->getLastAddedLinkId();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($linkId)
    {
        /** @var $link \Magento\Downloadable\Model\Link */
        $link = $this->linkFactory->create()->load($linkId);
        if (!$link->getId()) {
            throw new NoSuchEntityException('There is no downloadable link with provided ID.');
        }
        $link->delete();
        return true;
    }
}
