<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class DataFactoryTest extends \Magento\Test\AbstractFactoryTestCase
{
    protected function setUp()
    {
        $this->instanceClassName = 'Magento\Framework\App\Config\Data';
        $this->factoryClassName = 'Magento\Framework\App\Config\DataFactory';
        parent::setUp();
    }
}
