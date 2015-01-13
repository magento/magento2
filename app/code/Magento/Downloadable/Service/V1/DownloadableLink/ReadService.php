<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink;

class ReadService implements ReadServiceInterface
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
     * @var \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkInfoBuilder
     */
    protected $linkBuilder;

    /**
     * @var \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableSampleInfoBuilder
     */
    protected $sampleBuilder;

    /**
     * @var \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfoBuilder
     */
    protected $resourceBuilder;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Downloadable\Model\Product\Type $downloadableType
     * @param Data\DownloadableLinkInfoBuilder $linkBuilder
     * @param Data\DownloadableSampleInfoBuilder $sampleBuilder
     * @param Data\DownloadableResourceInfoBuilder $resourceBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\Product\Type $downloadableType,
        \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkInfoBuilder $linkBuilder,
        \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableSampleInfoBuilder $sampleBuilder,
        \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfoBuilder $resourceBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->linkBuilder = $linkBuilder;
        $this->sampleBuilder = $sampleBuilder;
        $this->resourceBuilder = $resourceBuilder;
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
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkInfo
     */
    protected function buildLink($resourceData)
    {
        $this->setBasicFields($resourceData, $this->linkBuilder);
        $this->linkBuilder->setPrice($resourceData->getPrice());
        $this->linkBuilder->setNumberOfDownloads($resourceData->getNumberOfDownloads());
        $this->linkBuilder->setShareable($resourceData->getIsShareable());
        $this->linkBuilder->setLinkResource($this->entityInfoGenerator('link', $resourceData));
        return $this->linkBuilder->create();
    }

    /**
     * Subroutine for buildLink and buildSample
     *
     * @param \Magento\Downloadable\Model\Link|\Magento\Downloadable\Model\Sample $resourceData
     * @param Data\DownloadableLinkInfoBuilder|Data\DownloadableSampleInfoBuilder $builder
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
        $builder->setSampleResource($this->entityInfoGenerator('sample', $resourceData));
    }

    /**
     * Build a sample data object
     *
     * @param \Magento\Downloadable\Model\Sample $resourceData
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableSampleInfo
     */
    protected function buildSample($resourceData)
    {
        $this->setBasicFields($resourceData, $this->sampleBuilder);
        return $this->sampleBuilder->create();
    }

    /**
     * Build file info data object
     *
     * @param string $entityType 'link' or 'sample'
     * @param \Magento\Downloadable\Model\Link|\Magento\Downloadable\Model\Sample $resourceData
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo|null
     */
    protected function entityInfoGenerator($entityType, $resourceData)
    {
        $type = $resourceData->getData($entityType . '_type');
        if (empty($type)) {
            return null;
        }
        $this->resourceBuilder->populateWithArray([]);
        $this->resourceBuilder->setType($type);
        $this->resourceBuilder->setUrl($resourceData->getData($entityType . '_url'));
        $this->resourceBuilder->setFile($resourceData->getData($entityType . '_file'));
        return $this->resourceBuilder->create();
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
}
