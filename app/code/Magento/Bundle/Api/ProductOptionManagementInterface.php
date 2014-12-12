<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Api;

interface ProductOptionManagementInterface
{
    /**
     * Add new option for bundle product
     *
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function save(\Magento\Bundle\Api\Data\OptionInterface $option);
}
