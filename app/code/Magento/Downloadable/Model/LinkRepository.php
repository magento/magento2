<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\Data\LinkInterface;
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
     * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory
     */
    protected $linkDataObjectFactory;

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
     * @param LinkFactory $linkFactory
     * @param Link\ContentValidator $contentValidator
     * @param EncoderInterface $jsonEncoder
     * @param ContentUploaderInterface $fileContentUploader
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\Product\Type $downloadableType,
        \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkDataObjectFactory,
        LinkFactory $linkFactory,
        Link\ContentValidator $contentValidator,
        EncoderInterface $jsonEncoder,
        ContentUploaderInterface $fileContentUploader
    ) {
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->linkDataObjectFactory = $linkDataObjectFactory;
        $this->linkFactory = $linkFactory;
        $this->contentValidator = $contentValidator;
        $this->jsonEncoder = $jsonEncoder;
        $this->fileContentUploader = $fileContentUploader;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        return $this->getLinksByProduct($product);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    public function getLinksByProduct(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $linkList = [];
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
     * Subroutine for build link
     *
     * @param \Magento\Downloadable\Model\Link $resourceData
     * @param \Magento\Downloadable\Api\Data\LinkInterface $dataObject
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($sku, LinkInterface $link, $isGlobalScopeContent = true)
    {
        $product = $this->productRepository->get($sku, true);
        if ($link->getId() !== null) {
            return $this->updateLink($product, $link, $isGlobalScopeContent);
        } else {
            if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
                throw new InputException(__('Product type of the product must be \'downloadable\'.'));
            }
            if (!$this->contentValidator->isValid($link)) {
                throw new InputException(__('Provided link information is invalid.'));
            }

            if (!in_array($link->getLinkType(), ['url', 'file'])) {
                throw new InputException(__('Invalid link type.'));
            }
            $title = $link->getTitle();
            if (empty($title)) {
                throw new InputException(__('Link title cannot be empty.'));
            }
            return $this->saveLink($product, $link, $isGlobalScopeContent);
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param LinkInterface $link
     * @param bool $isGlobalScopeContent
     * @return int
     */
    protected function saveLink(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        LinkInterface $link,
        $isGlobalScopeContent
    ) {
        $linkData = [
            'link_id' => $link->getid() === null ? 0 : $link->getid(),
            'is_delete' => 0,
            'type' => $link->getLinkType(),
            'sort_order' => $link->getSortOrder(),
            'title' => $link->getTitle(),
            'price' => $link->getPrice(),
            'number_of_downloads' => $link->getNumberOfDownloads(),
            'is_shareable' => $link->getIsShareable(),
        ];

        if ($link->getLinkType() == 'file' && $link->getLinkFile() === null) {
            $linkData['file'] = $this->jsonEncoder->encode(
                [
                    $this->fileContentUploader->upload($link->getLinkFileContent(), 'link_file'),
                ]
            );
        } elseif ($link->getLinkType() === 'url') {
            $linkData['link_url'] = $link->getLinkUrl();
        } else {
            //existing link file
            $linkData['file'] = $this->jsonEncoder->encode(
                [
                    [
                        'file' => $link->getLinkFile(),
                        'status' => 'old',
                    ]
                ]
            );
        }

        if ($link->getSampleType() == 'file' && $link->getSampleFile() === null) {
            $linkData['sample']['type'] = 'file';
            $linkData['sample']['file'] = $this->jsonEncoder->encode(
                [
                    $this->fileContentUploader->upload($link->getSampleFileContent(), 'link_sample_file'),
                ]
            );
        } elseif ($link->getSampleType() == 'url') {
            $linkData['sample']['type'] = 'url';
            $linkData['sample']['url'] = $link->getSampleUrl();
        }

        $downloadableData = ['link' => [$linkData]];
        $product->setDownloadableData($downloadableData);
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }
        $this->downloadableType->save($product);
        return $product->getLastAddedLinkId();
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param LinkInterface $link
     * @param bool $isGlobalScopeContent
     * @return mixed
     * @throws InputException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateLink(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        LinkInterface $link,
        $isGlobalScopeContent
    ) {
        /** @var $existingLink \Magento\Downloadable\Model\Link */
        $existingLink = $this->linkFactory->create()->load($link->getId());
        if (!$existingLink->getId()) {
            throw new NoSuchEntityException(__('There is no downloadable link with provided ID.'));
        }
        if ($existingLink->getProductId() != $product->getId()) {
            throw new InputException(__('Provided downloadable link is not related to given product.'));
        }
        $validateLinkContent = $link->getLinkFileContent() === null ? false : true;
        $validateSampleContent = $link->getSampleFileContent() === null ? false : true;
        if (!$this->contentValidator->isValid($link, $validateLinkContent, $validateSampleContent)) {
            throw new InputException(__('Provided link information is invalid.'));
        }
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }
        $title = $link->getTitle();
        if (empty($title)) {
            if ($isGlobalScopeContent) {
                throw new InputException(__('Link title cannot be empty.'));
            }
        }

        if ($link->getLinkType() == 'file' && $link->getLinkFileContent() === null) {
            $link->setLinkFile($existingLink->getLinkFile());
        }
        if ($link->getSampleType() == 'file' && $link->getSampleFileContent() === null) {
            $link->setSampleFile($existingLink->getSampleFile());
        }

        $this->saveLink($product, $link, $isGlobalScopeContent);
        return $existingLink->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        /** @var $link \Magento\Downloadable\Model\Link */
        $link = $this->linkFactory->create()->load($id);
        if (!$link->getId()) {
            throw new NoSuchEntityException(__('There is no downloadable link with provided ID.'));
        }
        $link->delete();
        return true;
    }
}
