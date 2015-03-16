<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMSC\Api\Data;

interface CustomAttributeDataObjectInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const NAME = 'name';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);
}
