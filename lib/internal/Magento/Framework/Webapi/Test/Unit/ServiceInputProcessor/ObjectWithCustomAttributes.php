<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
