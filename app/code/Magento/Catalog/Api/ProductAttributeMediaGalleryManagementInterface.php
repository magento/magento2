<?php
/**
 * Product Media Attribute Write Service
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @todo implement this interface as a \Magento\Catalog\Model\Product\Attribute\Media\GalleryManagement.
 * Move logic from service there.
 */
interface ProductAttributeMediaGalleryManagementInterface
{
    /**
     * Create new gallery entry
     *
     * @param string $productSku
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface $entryContent
     * @param int $storeId
     * @return int gallery entry ID
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function create(
        $productSku,
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry,
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface $entryContent,
        $storeId = 0
    );

    /**
     * Update gallery entry
     *
     * @param string $productSku
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function update(
        $productSku,
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry,
        $storeId = 0
    );

    /**
     * Remove gallery entry
     *
     * @param string $productSku
     * @param int $entryId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function remove($productSku, $entryId);

    /**
     * Return information about gallery entry
     *
     * @param string $productSku
     * @param int $imageId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface
     */
    public function get($productSku, $imageId);

    /**
     * Retrieve the list of gallery entries associated with given product
     *
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]
     */
    public function getList($productSku);
}
