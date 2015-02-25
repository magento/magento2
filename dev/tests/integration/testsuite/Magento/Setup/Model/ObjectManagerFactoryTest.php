<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Setup\Mvc\Bootstrap\InitParamListener;

class ObjectManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $locator = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');
        $locator->expects($this->once())->method('get')->with(InitParamListener::BOOTSTRAP_PARAM)->willReturn([]);
        $factory = new ObjectManagerFactory($locator);
        $this->assertInstanceOf('Magento\Framework\ObjectManagerInterface', $factory->create());
    }
}
