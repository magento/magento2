<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class UriFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $factory = new \Magento\CacheInvalidate\Model\UriFactory();
        $this->assertInstanceOf('\Zend\Uri\Uri', $factory->create());
    }
}
