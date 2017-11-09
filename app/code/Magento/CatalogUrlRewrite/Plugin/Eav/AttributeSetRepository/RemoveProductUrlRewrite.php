<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Eav\AttributeSetRepository;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Remove url rewrites for products with given attribute set.
 */
class RemoveProductUrlRewrite
{
    /**
     * @var int
     */
    private $chunkSize = 1000;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ProductUrlRewriteProcessor constructor.
     *
     * @param UrlPersistInterface $urlPersist
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(UrlPersistInterface $urlPersist, CollectionFactory $collectionFactory)
    {
        $this->urlPersist = $urlPersist;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Remove url rewrites for products with given attribute set.
     *
     * @param AttributeSetRepositoryInterface $subject
     * @param \Closure $proceed
     * @param AttributeSetInterface $attributeSet
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        AttributeSetRepositoryInterface $subject,
        \Closure $proceed,
        AttributeSetInterface $attributeSet
    ) {
        /** @var Collection $productCollection */
        $productCollection = $this->collectionFactory->create();
        $productCollection->addFieldToFilter('attribute_set_id', ['eq' => $attributeSet->getId()]);
        $productIds = $productCollection->getAllIds();
        $result = $proceed($attributeSet);
        if (!empty($productIds)) {
            $productIds = array_chunk($productIds, $this->chunkSize);
            foreach ($productIds as $ids) {
                $this->urlPersist->deleteByData(
                    [
                        UrlRewrite::ENTITY_ID => $ids,
                        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    ]
                );
            }
        }

        return $result;
    }
}
