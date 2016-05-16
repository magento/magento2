<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class BaseFactoryTest extends \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
{
    protected function setUp()
    {
        $this->instanceClassName = 'Magento\Framework\App\Config\Base';
        $this->factoryClassName = 'Magento\Framework\App\Config\BaseFactory';
        parent::setUp();
    }
}
