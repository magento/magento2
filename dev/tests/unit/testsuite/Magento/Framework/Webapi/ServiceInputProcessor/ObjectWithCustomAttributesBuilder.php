<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\ServiceInputProcessor;

use Magento\Framework\Api\ExtensibleObjectBuilder;
use Magento\Framework\Webapi\ServiceInputProcessor\TestService;

class ObjectWithCustomAttributesBuilder extends ExtensibleObjectBuilder
{
    /**
     * @var string[]
     */
    protected $customAttributesCodes = [TestService::CUSTOM_ATTRIBUTE_CODE];
}
