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

/**
 * Class LinkRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory
     */
    protected $linkDataObjectFactory;

    /**
     * @var \Magento\Downloadable\Api\Data\SampleInterfaceFactory
     */
    protected $sampleDataObjectFactory;

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
     * @param \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkDataObjectFactory
     * @param \Magento\Downloadable\Api\Data\SampleInterfaceFactory $sampleDataObjectFactory
     * @param LinkFactory $linkFactory
     * @param Link\ContentValidator $contentValidator
     * @param EncoderInterface $jsonEncoder
     * @param ContentUploaderInterface $fileContentUploader
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\Product\Type $downloadableType,
        \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkDataObjectFactory,
        \Magento\Downloadable\Api\Data\SampleInterfaceFactory $sampleDataObjectFactory,
        LinkFactory $linkFactory,
        Link\ContentValidator $contentValidator,
        EncoderInterface $jsonEncoder,
        ContentUploaderInterface $fileContentUploader
    ) {
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->linkDataObjectFactory = $linkDataObjectFactory;
        $this->sampleDataObjectFactory = $sampleDataObjectFactory;
        $this->linkFactory = $linkFactory;
        $this->contentValidator = $contentValidator;
        $this->jsonEncoder = $jsonEncoder;
        $this->fileContentUploader = $fileContentUploader;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks($sku)
    {
        $linkList = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
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
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->linkDataObjectFactory->create();
        $this->setBasicFields($resourceData, $link);
        $link->setPrice($resourceData->getPrice());
        $link->setNumberOfDownloads($resourceData->getNumberOfDownloads());
        $link->setIsShareable($resourceData->getIsShareable());
        $link->setLinkType($resourceData->getLinkType());
        $link->setLinkFile($resourceData->getLinkFile());
        $link->setLinkUrl($resourceData->getLinkUrl());

        return $link;
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
     */
    public function getSamples($sku)
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
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($sku, LinkContentInterface $linkContent, $linkId = null, $isGlobalScopeContent = false)
    {
        $product = $this->productRepository->get($sku, true);
        if ($linkId) {

            /** @var $link \Magento\Downloadable\Model\Link */
            $link = $this->linkFactory->create()->load($linkId);
            if (!$link->getId()) {
                throw new NoSuchEntityException(__('There is no downloadable link with provided ID.'));
            }
            if ($link->getProductId() != $product->getId()) {
                throw new InputException(__('Provided downloadable link is not related to given product.'));
            }
            if (!$this->contentValidator->isValid($linkContent)) {
                throw new InputException(__('Provided link information is invalid.'));
            }
            if ($isGlobalScopeContent) {
                $product->setStoreId(0);
            }
            $title = $linkContent->getTitle();
            if (empty($title)) {
                if ($isGlobalScopeContent) {
                    throw new InputException(__('Link title cannot be empty.'));
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
            return $link->getId();
        } else {
            $product = $this->productRepository->get($sku, true);
            if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
                throw new InputException(__('Product type of the product must be \'downloadable\'.'));
            }
            if (!$this->contentValidator->isValid($linkContent)) {
                throw new InputException(__('Provided link information is invalid.'));
            }

            if (!in_array($linkContent->getLinkType(), ['url', 'file'])) {
                throw new InputException(__('Invalid link type.'));
            }
            $title = $linkContent->getTitle();
            if (empty($title)) {
                throw new InputException(__('Link title cannot be empty.'));
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
            throw new NoSuchEntityException(__('There is no downloadable link with provided ID.'));
        }
        $link->delete();
        return true;
    }
}
