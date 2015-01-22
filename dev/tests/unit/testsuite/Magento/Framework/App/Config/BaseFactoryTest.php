<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class BaseFactoryTest extends \Magento\Test\AbstractFactoryTestCase
{
    protected function setUp()
    {
        $this->instanceClassName = 'Magento\Framework\App\Config\Base';
        $this->factoryClassName = 'Magento\Framework\App\Config\BaseFactory';
        parent::setUp();
    }
}
