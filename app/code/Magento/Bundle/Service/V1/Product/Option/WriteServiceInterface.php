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
namespace Magento\Bundle\Service\V1\Product\Option;

interface WriteServiceInterface
{
    /**
     * Remove bundle option
     *
     * @param string $productSku
     * @param int $optionId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function remove($productSku, $optionId);

    /**
     * Add new option for bundle product
     *
     * @param string $productSku
     * @param \Magento\Bundle\Service\V1\Data\Product\Option $option
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function add($productSku, \Magento\Bundle\Service\V1\Data\Product\Option $option);

    /**
     * Update option for bundle product
     *
     * @param string $productSku
     * @param int $optionId
     * @param \Magento\Bundle\Service\V1\Data\Product\Option $option
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function update($productSku, $optionId, \Magento\Bundle\Service\V1\Data\Product\Option $option);
}
