<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Eav\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\UrlRewrite\Model\UrlPersistInterface;

class AttributeSetRepository
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist
     */
    private $urlPersist;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     */
    private $productCollection;

    /**
     * AttributeSetRepository constructor.
     * @param \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     */
    public function __construct(UrlPersistInterface $urlPersist, Collection $productCollection)
    {
        $this->urlPersist = $urlPersist;
        $this->productCollection = $productCollection;
    }

    /**
     * Remove product url rewrites when an attribute set is deleted.
     *
     * @param \Magento\Eav\Model\AttributeSetRepository $subject
     * @param callable $proceed
     * @param AttributeSetInterface $attributeSet
     * @return bool
     * @throws CouldNotDeleteException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundDelete(
        \Magento\Eav\Model\AttributeSetRepository $subject,
        callable $proceed,
        AttributeSetInterface $attributeSet
    ) {
        $attributeSetId = $attributeSet->getAttributeSetId();

        // Get the product ids
        $entityIDs = $attributeSetId
            ? $this->productCollection->addFieldToFilter('attribute_set_id', $attributeSetId)->getAllIds()
            : [];

        // Delete the attribute set
        $result = $proceed($attributeSet);

        // Delete the old product url rewrites
        if (!empty($entityIDs)) {
            try {
                $this->urlPersist->deleteByData(['entity_id' => $entityIDs, 'entity_type' => 'product']);
            } catch (\Exception $exception) {
                throw new CouldNotDeleteException(__('Could not delete the url rewrite(s): %1', $exception->getMessage()));
            }
        }
        return $result;
    }
}
