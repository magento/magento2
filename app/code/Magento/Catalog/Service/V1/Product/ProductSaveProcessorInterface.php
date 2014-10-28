<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Service\V1\Product;

/**
 * Interface for product data modification.
 */
interface ProductSaveProcessorInterface
{
    /**
     * Create product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Service\V1\Data\Product $productData
     * @return string id
     */
    public function create(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Service\V1\Data\Product $productData
    );

    /**
     * Create product after the initial creation is complete.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Service\V1\Data\Product $productData
     * @return string id
     */
    public function afterCreate(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Service\V1\Data\Product $productData
    );

    /**
     * Update product.
     *
     * @param string $sku
     * @param \Magento\Catalog\Service\V1\Data\Product $product
     * @return string id
     */
    public function update($sku, \Magento\Catalog\Service\V1\Data\Product $product);

    /**
     * Delete product.
     *
     * @param \Magento\Catalog\Service\V1\Data\Product $product
     * @return void
     */
    public function delete(\Magento\Catalog\Service\V1\Data\Product $product);
}
