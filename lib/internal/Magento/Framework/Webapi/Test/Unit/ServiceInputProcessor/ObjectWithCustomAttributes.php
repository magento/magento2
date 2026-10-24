<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class ObjectWithCustomAttributes extends AbstractExtensibleObject
{
    /**
     * @var string[]
     */
    protected $customAttributesCodes = [TestService::CUSTOM_ATTRIBUTE_CODE];
}
