<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Api;

/**
 * Interface Product links handling interface
 */
interface ProductLinkRepositoryInterface
{
    /**
     * Save product link
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface $entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity);

    /**
     * Delete product link
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface $entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function delete(\Magento\Catalog\Api\Data\ProductLinkInterface $entity);

    /**
     * @param string $productSku
     * @param string $type
     * @param string $linkedProductSku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function deleteById($productSku, $type, $linkedProductSku);
}
