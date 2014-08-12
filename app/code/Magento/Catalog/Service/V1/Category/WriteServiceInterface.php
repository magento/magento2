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
namespace Magento\Catalog\Service\V1\Category;

interface WriteServiceInterface
{
    /**
     * Create category service
     *
     * @param \Magento\Catalog\Service\V1\Data\Category $category
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function create(\Magento\Catalog\Service\V1\Data\Category $category);

    /**
     * Delete category
     *
     * @param int $categoryId category which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete($categoryId);

    /**
     * Update category
     *
     * @param int $categoryId category to be updated
     * @param \Magento\Catalog\Service\V1\Data\Category $category
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function update($categoryId, \Magento\Catalog\Service\V1\Data\Category $category);

    /**
     * Move category
     *
     * @param int $categoryId
     * @param int $parentId
     * @param int $afterId
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function move($categoryId, $parentId, $afterId = null);
}
