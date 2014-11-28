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

namespace Magento\Catalog\Api;

interface ProductCustomOptionRepositoryInterface
{
    /**
     * Get the list of custom options for a specific product
     *
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     */
    public function getList($productSku);

    /**
     * Get custom option for a specific product
     *
     * @param string $productSku
     * @param int $optionId
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface
     */
    public function get($productSku, $optionId);

    /**
     * Delete custom option from product
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @return bool
     */
    public function delete(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option);

    /**
     * Save custom option
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface
     */
    public function save(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option);

    /**
     * @param string $productSku
     * @param int $optionId
     * @return bool
     */
    public function deleteByIdentifier($productSku, $optionId);
}
