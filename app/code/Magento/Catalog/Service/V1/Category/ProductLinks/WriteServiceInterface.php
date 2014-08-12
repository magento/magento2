<?php
/**
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
namespace Magento\Catalog\Service\V1\Category\ProductLinks;

use Magento\Catalog\Service\V1\Data\Category\ProductLink;

interface WriteServiceInterface
{
    /**
     * Assign a product to the required category
     *
     * @param int $categoryId
     * @param \Magento\Catalog\Service\V1\Data\Category\ProductLink $productLink
     * @return bool Will returned True if assigned
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function assignProduct($categoryId, ProductLink $productLink);

    /**
     * @param int $categoryId
     * @param \Magento\Catalog\Service\V1\Data\Category\ProductLink $productLink
     * @return bool Will returned True if updated
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function updateProduct($categoryId, ProductLink $productLink);

    /**
     * Remove the product assignment from the category.
     *
     * @param int $categoryId
     * @param string $productSku Product SKU
     * @return bool Will returned True if products sucessfully deleted
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function removeProduct($categoryId, $productSku);
}
