<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\Config\Converter;
use Magento\Framework\Api\Config\Reader;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinData;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory;
use Magento\Framework\Reflection\TypeProcessor;

class ExtensionAttributesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Api\ExtensionAttributesFactory */
    private $factory;

    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReader;

    /**
     * @var ExtensionAttributeJoinDataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * @var TypeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeProcessor;

    protected function setUp()
    {
        $this->configReader = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributeJoinDataFactory = $this
            ->getMockBuilder('Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributeJoinDataFactory = $this
            ->getMockBuilder('Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeProcessor = $this->getMockBuilder('Magento\Framework\Reflection\TypeProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/_files/Magento/Wonderland'));
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->factory = new ExtensionAttributesFactory(
            $objectManager,
            $this->configReader,
            $this->extensionAttributeJoinDataFactory,
            $this->typeProcessor
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

    /**
     * Test the processing of the join config for a particular type
     */
    public function testProcess()
    {
        $this->configReader->expects($this->once())
            ->method('read')
            ->will($this->returnValue($this->getConfig()));

        $collection = $this->getMockBuilder('Magento\Framework\Data\Collection\Db')
            ->setMethods(['joinExtensionAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $extensionAttributeJoinData = new ExtensionAttributeJoinData();
        $this->extensionAttributeJoinDataFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributeJoinData);

        $collection->expects($this->once())->method('joinExtensionAttribute')->with($extensionAttributeJoinData);

        $this->factory->process($collection, 'Magento\Catalog\Api\Data\ProductInterface');
        $this->assertEquals('reviews', $extensionAttributeJoinData->getReferenceTable());
        $this->assertEquals('extension_attribute_review_id', $extensionAttributeJoinData->getReferenceTableAlias());
        $this->assertEquals('product_id', $extensionAttributeJoinData->getReferenceField());
        $this->assertEquals('id', $extensionAttributeJoinData->getJoinField());
        $this->assertEquals('review_id', $extensionAttributeJoinData->getSelectField());
    }

    private function getConfig() {
        return [
            'Magento\Catalog\Api\Data\ProductInterface' => [
                'review_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_REFERENCE_FIELD => "product_id",
                        Converter::JOIN_SELECT_FIELDS => "review_id",
                        Converter::JOIN_JOIN_ON_FIELD => "id",
                    ],
                ],
            ],
            'Magento\Customer\Api\Data\CustomerInterface' => [
                'library_card_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "library_account",
                        Converter::JOIN_SELECT_FIELDS => "library_card_id",
                        Converter::JOIN_JOIN_ON_FIELD => "customer_id",
                    ],
                ],
                'reviews' => [
                    Converter::DATA_TYPE => 'Magento\Reviews\Api\Data\Reviews[]',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_SELECT_FIELDS => "comment,rating",
                        Converter::JOIN_JOIN_ON_FIELD => "customer_id",
                    ],
                ],
            ],
        ];
    }
}
