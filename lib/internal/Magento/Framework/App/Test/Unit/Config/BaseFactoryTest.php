<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class BaseFactoryTest extends \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
{
    protected function setUp(): void
    {
        $this->instanceClassName = \Magento\Framework\App\Config\Base::class;
        $this->factoryClassName = \Magento\Framework\App\Config\BaseFactory::class;
        parent::setUp();
    }
}
