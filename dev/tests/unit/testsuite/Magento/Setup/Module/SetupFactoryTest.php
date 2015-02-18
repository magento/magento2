<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

class SetupFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SetupFactory
     */
    private $setupFactory;

    protected function setUp()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $this->setupFactory = new SetupFactory($serviceLocatorMock);
    }

    public function testCreateSetup()
    {
        $resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $setup = $this->setupFactory->createSetup($resourceMock);
        $this->assertInstanceOf('Magento\Setup\Module\Setup', $setup);
    }
}
