<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model\Plugin\Product;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Mod\HelloWorldApi\Api\Data\ExtraCommentInterface;
use Mod\HelloWorldApi\Model\ExtraComment;
use Mod\HelloWorldApi\Model\ExtraCommentFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Extra comments Repository plugin class.
 */
class Repository
{
    /** @var ProductExtensionFactory */
    private $productExtensionFactory;

    /** @var ProductInterface */
    private $currentProduct;

    /** @var  EntityManager */
    private $entityManager;

    /** @var MetadataPool */
    private $metadataPool;

    /** @var  ResourceConnection\ */
    private $resourceConnection;

    /** @var  ExtraCommentFactory */
    private $extraCommentFactory;

    /**
     * Repository constructor.
     * @param ProductExtensionFactory $productExtensionFactory
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ExtraCommentFactory $extraCommentFactory
     */
    public function __construct(
        ProductExtensionFactory $productExtensionFactory,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ExtraCommentFactory $extraCommentFactory
    ) {
        $this->productExtensionFactory = $productExtensionFactory;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->extraCommentFactory = $extraCommentFactory;
    }

    /**
     * Add Extra Abilities to customer extension attributes
     *
     * @param ProductRepositoryInterface $subject
     * @param SearchResults $searchResult
     * @return SearchResults
     * @throws \Exception
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        SearchResults $searchResult
    ) {
        /** @var ProductInterface $product */
        foreach ($searchResult->getItems() as $product) {
            $this->addExtraCommentsToProduct($product);
        }

        return $searchResult;
    }

    /**
     * Before save plugin.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @return void
     */
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product
    ) {
        $this->currentProduct = $product;
    }

    /**
     * After get by id plugin.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws \Exception
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $product
    ) {
        $this->addExtraCommentsToProduct($product);
        return $product;
    }

    /**
     * After save plugin.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws \Exception
     */
    public function afterSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product
    ) {
        if ($this->currentProduct !== null) {
            /** @var ProductInterface $previosCustomer */
            $extensionAttributes = $this->currentProduct->getExtensionAttributes();

            if ($extensionAttributes && $extensionAttributes->getExtraComments()) {
                /** @var ExtraComment $extraComments */
                $extraComments = $extensionAttributes->getExtraComments();
                if (is_array($extraComments)) {
                    /** @var ExtraCommentInterface $extraComment */
                    foreach ($extraComments as $extraComment) {
                        $extraComment->setProductSku($product->getSku());
                        $this->entityManager->save($extraComment);
                    }
                }
            }
            $this->currentProduct = null;
        }

        return $product;
    }

    /**
     * Add extra abilities to the current customer.
     *
     * @param ProductInterface $product
     * @return self
     * @throws \Exception
     */
    private function addExtraCommentsToProduct(ProductInterface $product)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->productExtensionFactory->create();
        }

        $extraComments = [];
        $metadata = $this->metadataPool->getMetadata(ExtraCommentInterface::class);
        $connection = $this->resourceConnection->getConnection();

        $select = $connection
            ->select()
            ->from($metadata->getEntityTable(), ExtraCommentInterface::COMMENT_ID)
            ->where(ExtraCommentInterface::PRODUCT_SKU . ' = ?', $product->getSku());
        $ids = $connection->fetchCol($select);

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $extraComment = $this->extraCommentFactory->create();
                $extraComments[] = $this->entityManager->load($extraComment, $id);
            }
        }
        $extensionAttributes->setExtraComments($extraComments);
        $product->setExtensionAttributes($extensionAttributes);

        return $this;
    }
}
