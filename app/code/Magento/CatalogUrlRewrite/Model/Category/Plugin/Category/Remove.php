<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Remove
{
    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var array */
    private $categoryIds;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider
    ) {
        $this->urlPersist = $urlPersist;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
    }

    /**
     * Save category ids before delete
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Magento\Framework\DataObject|int|string $object
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(\Magento\Catalog\Model\ResourceModel\Category $subject, $object)
    {
        if ($object instanceof CategoryInterface) {
            $this->categoryIds = $this->childrenCategoriesProvider->getChildrenIds($object, true);
            $this->categoryIds[] = $object->getId();
        }
        return [$object];
    }

    /**
     * Remove product urls from storage
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Magento\Catalog\Model\ResourceModel\Category $result
     * @param \Magento\Framework\DataObject|int|string $object
     * @return \Magento\Catalog\Model\ResourceModel\Category
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Magento\Catalog\Model\ResourceModel\Category $result,
        $object
    ) {
        if ($object instanceof CategoryInterface) {
            foreach ($this->categoryIds as $categoryId) {
                $this->deleteRewritesForCategory($categoryId);
            }
        }
        return $result;
    }

    /**
     * Remove url rewrites by categoryId
     *
     * @param int $categoryId
     * @return void
     */
    protected function deleteRewritesForCategory($categoryId)
    {
        $this->urlPersist->deleteByData(
            [
                UrlRewrite::ENTITY_ID => $categoryId,
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );
        $this->urlPersist->deleteByData(
            [
                UrlRewrite::METADATA => serialize(['category_id' => $categoryId]),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );
    }
}
