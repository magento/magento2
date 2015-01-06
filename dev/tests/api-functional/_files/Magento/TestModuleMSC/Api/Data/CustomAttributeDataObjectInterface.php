<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModuleMSC\Api\Data;

interface CustomAttributeDataObjectInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const NAME = 'name';

    /**
     * @return string
     */
    public function getName();
}
