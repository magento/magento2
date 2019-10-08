<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Api\Data\File\ContentUploaderInterface;
use Magento\Downloadable\Model\Product\TypeHandler\Link as LinkHandler;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\ObjectManager;

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
     * @var Type
     */
    protected $downloadableType;

    /**
     * @var ContentUploaderInterface
     */
    protected $fileContentUploader;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var LinkHandler
     */
    private $linkTypeHandler;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

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
     * @inheritdoc
     */
    public function getList($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        return $this->getLinksByProduct($product);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws InputException
     */
    public function save($sku, LinkInterface $link, $isGlobalScopeContent = true)
    {
        $product = $this->productRepository->get($sku, true);
        if ($link->getId() !== null) {
            return $this->updateLink($product, $link, $isGlobalScopeContent);
        } else {
            if ($product->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
                throw new InputException(
                    __('The product needs to be the downloadable type. Verify the product and try again.')
                );
            }
            $this->validateLinkType($link);
            $this->validateSampleType($link);
            if (!$this->contentValidator->isValid($link, true, $link->hasSampleType())) {
                throw new InputException(__('The link information is invalid. Verify the link and try again.'));
            }
            $title = $link->getTitle();
            if (empty($title)) {
                throw new InputException(__('The link title is empty. Enter the link title and try again.'));
            }

            return $this->saveLink($product, $link, $isGlobalScopeContent);
        }
    }

    /**
     * Construct Data structure and Save it.
     *
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
            'link_id' => (int)$link->getId(),
            'is_delete' => 0,
            'type' => $link->getLinkType(),
            'sort_order' => $link->getSortOrder(),
            'title' => $link->getTitle(),
            'price' => $link->getPrice(),
            'number_of_downloads' => $link->getNumberOfDownloads(),
            'is_shareable' => $link->getIsShareable(),
        ];

        if ($link->getLinkType() == 'file' && $link->getLinkFileContent() !== null) {
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

        if ($link->getSampleType() == 'file') {
            $linkData['sample']['type'] = 'file';
            if ($link->getSampleFileContent() !== null) {
                $fileData = [
                    $this->fileContentUploader->upload($link->getSampleFileContent(), 'link_sample_file'),
                ];
            } else {
                $fileData = [
                    [
                        'file' => $link->getSampleFile(),
                        'status' => 'old',
                    ]
                ];
            }
            $linkData['sample']['file'] = $this->jsonEncoder->encode($fileData);
        } elseif ($link->getSampleType() == 'url') {
            $linkData['sample']['type'] = 'url';
            $linkData['sample']['url'] = $link->getSampleUrl();
        }

        $downloadableData = ['link' => [$linkData]];
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }
        $this->getLinkTypeHandler()->save($product, $downloadableData);
        return $product->getLastAddedLinkId();
    }

    /**
     * Update existing Link.
     *
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
            throw new NoSuchEntityException(
                __('No downloadable link with the provided ID was found. Verify the ID and try again.')
            );
        }
        $linkFieldValue = $product->getData(
            $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
        );
        if ($existingLink->getProductId() != $linkFieldValue) {
            throw new InputException(
                __("The downloadable link isn't related to the product. Verify the link and try again.")
            );
        }
        $this->validateLinkType($link);
        $this->validateSampleType($link);
        $validateSampleContent = $link->hasSampleType();
        if (!$this->contentValidator->isValid($link, true, $validateSampleContent)) {
            throw new InputException(__('The link information is invalid. Verify the link and try again.'));
        }
        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }
        $title = $link->getTitle();
        if (empty($title)) {
            if ($isGlobalScopeContent) {
                throw new InputException(__('The link title is empty. Enter the link title and try again.'));
            }
        }
        if (!$validateSampleContent) {
            $this->resetLinkSampleContent($link, $existingLink);
        }
        $this->saveLink($product, $link, $isGlobalScopeContent);

        return $existingLink->getId();
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        /** @var $link \Magento\Downloadable\Model\Link */
        $link = $this->linkFactory->create()->load($id);
        if (!$link->getId()) {
            throw new NoSuchEntityException(
                __('No downloadable link with the provided ID was found. Verify the ID and try again.')
            );
        }
        try {
            $link->delete();
        } catch (\Exception $exception) {
            throw new StateException(__('The link with "%1" ID can\'t be deleted.', $link->getId()), $exception);
        }
        return true;
    }

    /**
     * Check that Link type exist.
     *
     * @param LinkInterface $link
     * @return void
     * @throws InputException
     */
    private function validateLinkType(LinkInterface $link): void
    {
        if (!in_array($link->getLinkType(), ['url', 'file'], true)) {
            throw new InputException(__('The link type is invalid. Verify and try again.'));
        }
    }

    /**
     * Check that Link sample type exist.
     *
     * @param LinkInterface $link
     * @return void
     * @throws InputException
     */
    private function validateSampleType(LinkInterface $link): void
    {
        if ($link->hasSampleType() && !in_array($link->getSampleType(), ['url', 'file'], true)) {
            throw new InputException(__('The link sample type is invalid. Verify and try again.'));
        }
    }

    /**
     * Reset Sample type and file.
     *
     * @param LinkInterface $link
     * @param LinkInterface $existingLink
     * @return void
     */
    private function resetLinkSampleContent(LinkInterface $link, LinkInterface $existingLink): void
    {
        $existingType = $existingLink->getSampleType();
        $link->setSampleType($existingType);
        if ($existingType === 'file') {
            $link->setSampleFile($existingLink->getSampleFile());
        } else {
            $link->setSampleUrl($existingLink->getSampleUrl());
        }
    }

    /**
     * Get MetadataPool instance
     *
     * @deprecated 100.1.0 MAGETWO-52273
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }

        return $this->metadataPool;
    }

    /**
     * Get LinkTypeHandler  instance
     *
     * @deprecated 100.1.0 MAGETWO-52273
     * @return LinkHandler
     */
    private function getLinkTypeHandler()
    {
        if (!$this->linkTypeHandler) {
            $this->linkTypeHandler = ObjectManager::getInstance()->get(LinkHandler::class);
        }

        return $this->linkTypeHandler;
    }
}
