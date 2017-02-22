<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

class ExtensionAttributesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Api\ExtensionAttributesFactory */
    private $factory;

    protected function setUp()
    {
        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/_files/Magento/Wonderland'));
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->factory = $objectManager->create(
            'Magento\Framework\Api\ExtensionAttributesFactory',
            ['objectManager' => $objectManager]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateThrowExceptionIfInterfaceNotImplemented()
    {
        $this->factory->create('Magento\Framework\Api\ExtensionAttributesFactoryTest');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateThrowExceptionIfInterfaceNotOverridden()
    {
        $this->factory->create('\Magento\Wonderland\Model\Data\FakeExtensibleOne');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateThrowExceptionIfReturnIsIncorrect()
    {
        $this->factory->create('\Magento\Wonderland\Model\Data\FakeExtensibleTwo');
    }

    public function testCreate()
    {
        $this->assertInstanceOf(
            'Magento\Wonderland\Api\Data\FakeRegionExtension',
            $this->factory->create('Magento\Wonderland\Model\Data\FakeRegion')
        );
    }

    public function testCreateWithLogicException()
    {
        $this->setExpectedException(
            'LogicException',
            "Class 'Magento\\Framework\\Api\\ExtensionAttributesFactoryTest' must implement an interface, "
            . "which extends from 'Magento\\Framework\\Api\\ExtensibleDataInterface'"
        );
        $this->factory->create(get_class($this));
    }
}
