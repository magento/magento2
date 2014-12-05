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

namespace Magento\Catalog\Api;

interface CategoryRepositoryInterface
{
    /**
     * Create category service
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Catalog\Api\Data\CategoryInterface $category);

    /**
     * Get info about category by category id
     *
     * @param int $categoryId
     * @param int $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($categoryId, $storeId = null);

    /**
     * Delete category by identifier
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category category which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Magento\Catalog\Api\Data\CategoryInterface $category);


    /**
     * Delete category by identifier
     *
     * @param int $categoryId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteByIdentifier($categoryId);
}
