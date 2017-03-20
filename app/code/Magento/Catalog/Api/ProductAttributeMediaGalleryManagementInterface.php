<?php
/**
 * Product Media Attribute Write Service
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @todo implement this interface as a \Magento\Catalog\Model\Product\Attribute\Media\GalleryManagement.
 * Move logic from service there.
 * @api
 */
interface ProductAttributeMediaGalleryManagementInterface
{
    /**
     * Create new gallery entry
     *
     * @param string $sku
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry
     * @return int gallery entry ID
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function create(
        $sku,
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry
    );

    /**
     * Update gallery entry
     *
     * @param string $sku
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function update(
        $sku,
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry
    );

    /**
     * Remove gallery entry
     *
     * @param string $sku
     * @param int $entryId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function remove($sku, $entryId);

    /**
     * Return information about gallery entry
     *
     * @param string $sku
     * @param int $entryId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface
     */
    public function get($sku, $entryId);

    /**
     * Retrieve the list of gallery entries associated with given product
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]
     */
    public function getList($sku);
}
