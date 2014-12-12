<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Api;

interface LinkManagementInterface
{
    /**
     * Get all children for Bundle product
     *
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getChildren($productSku);

    /**
     * @param  string $productSku
     * @param  string $childSku
     * @return bool
     */
    public function addChild($productSku, $childSku);

    /**
     * Remove configurable product option
     *
     * @param string $productSku
     * @param string $childSku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     * @return bool
     */
    public function removeChild($productSku, $childSku);
}
