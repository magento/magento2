<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\LinkInterface;
use \Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Downloadable\Model\Link\ContentValidator;
use Magento\Downloadable\Helper\File;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Downloadable\Model\ResourceModel\Link as LinkResource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class LinkRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkRepository implements \Magento\Downloadable\Api\LinkRepositoryInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var DownloadableType
     */
    protected $downloadableType;

    /**
     * @var LinkResource
     */
    protected $resourceModel;

    /**
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var ContentValidator
     */
    protected $contentValidator;

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
     * @param ProductRepository $productRepository
     * @param DownloadableType $downloadableType
     * @param LinkResource $resourceModel
     * @param LinkInterfaceFactory $linkFactory
     * @param ContentValidator $contentValidator
     * @param JsonHelper $jsonHelper
     * @param File $downloadableFile
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductRepository $productRepository,
        DownloadableType $downloadableType,
        LinkResource $resourceModel,
        LinkInterfaceFactory $linkFactory,
        ContentValidator $contentValidator,
        JsonHelper $jsonHelper,
        File $downloadableFile
    ) {
        $this->metadataPool = $metadataPool;
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->resourceModel = $resourceModel;
        $this->linkFactory = $linkFactory;
        $this->contentValidator = $contentValidator;
        $this->jsonHelper = $jsonHelper;
        $this->downloadableFile = $downloadableFile;
    }

    /**
     * List of links with associated samples
     *
     * @param string $sku
     * @return LinkInterface[]
     */
    public function getList($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        return $this->getLinksByProduct($product);
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getLinksByProduct(ProductInterface $product)
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
        $link = $this->linkFactory->create();
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
        //ToDo: before validation link should have link_file_content
        //if (!$this->contentValidator->isValid($link)) {
        //    throw new InputException(__('Provided link information is invalid.'));
        //}

        if (!in_array($link->getLinkType(), ['url', 'file'])) {
            throw new InputException(__('Invalid link type.'));
        }
        $title = $link->getTitle();
        if (empty($title)) {
            throw new InputException(__('Link title cannot be empty.'));
        }
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $link->setProductId($product->getData($metadata->getLinkField()));
        $this->setFiles($link);
        $this->resourceModel->save($link);
        return $link->getId();
    }

    /**
     * Load files and set paths to link and sample of link
     *
     * @param LinkInterface $link
     * @return void
     */
    protected function setFiles(LinkInterface $link)
    {
        if ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE
            && $link->getSampleFileData()
        ) {
            $linkSampleFileName = $this->downloadableFile->moveFileFromTmp(
                $link->getBaseSampleTmpPath(),
                $link->getBaseSamplePath(),
                $this->jsonHelper->jsonDecode($link->getSampleFileData())
            );
            $link->setSampleFile($linkSampleFileName);
            $link->setSampleUrl(null);
        }
        if ($link->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE && $link->getFile()) {
            $linkFileName = $this->downloadableFile->moveFileFromTmp(
                $link->getBaseTmpPath(),
                $link->getBasePath(),
                $this->jsonHelper->jsonDecode($link->getFile())
            );
            $link->setLinkFile($linkFileName);
            $link->setLinkUrl(null);
        }
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
        try {
            $this->resourceModel->delete($link);
        } catch (\Exception $exception) {
            throw new StateException(__('Cannot delete link with id %1', $link->getId()), $exception);
        }
        return true;
    }
}
