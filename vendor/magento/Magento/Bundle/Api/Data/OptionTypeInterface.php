<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Api\Data;

interface OptionTypeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get type label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get type code
     *
     * @return string
     */
    public function getCode();
}
