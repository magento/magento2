<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\AttributeSetRepository;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;

/**
 * Delete related products after attribute set successfully removed.
 */
class RemoveProducts
{
    /**
     * Retrieve products related to specific attribute set.
     *
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * RemoveProducts constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Delete related to specific attribute set products, if attribute set was removed successfully.
     *
     * @param AttributeSetRepositoryInterface $subject
     * @param bool $result
     * @param AttributeSetInterface $attributeSet
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        AttributeSetRepositoryInterface $subject,
        bool $result,
        AttributeSetInterface $attributeSet
    ) {
        /** @var Collection $productCollection */
        $productCollection = $this->collectionFactory->create();
        $productCollection->addFieldToFilter('attribute_set_id', ['eq' => $attributeSet->getId()]);
        $productCollection->delete();

        return $result;
    }
}
