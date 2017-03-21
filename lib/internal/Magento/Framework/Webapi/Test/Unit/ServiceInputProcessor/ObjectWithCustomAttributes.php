<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class ObjectWithCustomAttributes extends AbstractExtensibleObject
{
    /**
     * @var string[]
     */
    protected $customAttributesCodes = [TestService::CUSTOM_ATTRIBUTE_CODE];
}
