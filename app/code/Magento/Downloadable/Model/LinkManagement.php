<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Model;


class LinkManagement implements \Magento\Downloadable\Api\LinkManagementInterface
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
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Downloadable\Model\Product\Type $downloadableType
     * @param \Magento\Downloadable\Api\Data\LinkDataBuilder $linkBuilder
     * @param \Magento\Downloadable\Api\Data\SampleDataBuilder $sampleBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\Product\Type $downloadableType,
        \Magento\Downloadable\Api\Data\LinkDataBuilder $linkBuilder,
        \Magento\Downloadable\Api\Data\SampleDataBuilder $sampleBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->downloadableType = $downloadableType;
        $this->linkBuilder = $linkBuilder;
        $this->sampleBuilder = $sampleBuilder;
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

}
