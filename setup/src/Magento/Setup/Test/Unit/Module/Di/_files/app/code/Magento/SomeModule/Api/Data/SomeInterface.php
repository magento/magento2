<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Api\Data;

interface SomeInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**
     * @return \Magento\Eav\Api\Data\AttributeExtensionInterface|null
     */
    public function getExtensionAttributes();
}
