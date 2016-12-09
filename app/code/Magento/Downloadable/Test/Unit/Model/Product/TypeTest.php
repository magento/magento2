<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Product;

use Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface;

/**
 * Class TypeTest
 * Test for \Magento\Downloadable\Model\Product\Type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    private $target;

    /**
     * @var TypeHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeHandler;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);
        $fileStorageDb = $this->getMockBuilder(
            \Magento\MediaStorage\Helper\File\Storage\Database::class
        )->disableOriginalConstructor()->getMock();
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $productFactoryMock = $this->getMock(\Magento\Catalog\Model\ProductFactory::class, [], [], '', false);
        $sampleResFactory = $this->getMock(
            \Magento\Downloadable\Model\ResourceModel\SampleFactory::class,
            [],
            [],
            '',
            false
        );
        $linkResource = $this->getMock(\Magento\Downloadable\Model\ResourceModel\Link::class, [], [], '', false);
        $linksFactory = $this->getMock(
            \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory::class,
            [],
            [],
            '',
            false
        );
        $samplesFactory = $this->getMock(
            \Magento\Downloadable\Model\ResourceModel\Sample\CollectionFactory::class,
            [],
            [],
            '',
            false
        );
        $sampleFactory = $this->getMock(\Magento\Downloadable\Model\SampleFactory::class, [], [], '', false);
        $linkFactory = $this->getMock(\Magento\Downloadable\Model\LinkFactory::class, [], [], '', false);

        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $resourceProductMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product::class,
            ['getEntityType'],
            [],
            '',
            false
        );
        $resourceProductMock->expects($this->any())->method('getEntityType')->will($this->returnValue($entityTypeMock));

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getResource',
                'canAffectOptions',
                'getLinksPurchasedSeparately',
                'setTypeHasRequiredOptions',
                'setRequiredOptions',
                'getDownloadableData',
                'setTypeHasOptions',
                'setLinksExist',
                'getDownloadableLinks',
                '__wakeup',
            ],
            [],
            '',
            false
        );
        $this->product->expects($this->any())->method('getResource')->will($this->returnValue($resourceProductMock));
        $this->product->expects($this->any())->method('setTypeHasRequiredOptions')->with($this->equalTo(true))->will(
            $this->returnSelf()
        );
        $this->product->expects($this->any())->method('setRequiredOptions')->with($this->equalTo(true))->will(
            $this->returnSelf()
        );
        $this->product->expects($this->any())->method('setTypeHasOptions')->with($this->equalTo(false));
        $this->product->expects($this->any())->method('setLinksExist')->with($this->equalTo(false));
        $this->product->expects($this->any())->method('canAffectOptions')->with($this->equalTo(true));
        $this->product->expects($this->any())->method('getLinksPurchasedSeparately')->will($this->returnValue(true));
        $this->product->expects($this->any())->method('getLinksPurchasedSeparately')->will($this->returnValue(true));

        $eavConfigMock = $this->getMock(\Magento\Eav\Model\Config::class, ['getEntityAttributeCodes'], [], '', false);
        $eavConfigMock->expects($this->any())
            ->method('getEntityAttributeCodes')
            ->with($this->equalTo($entityTypeMock), $this->equalTo($this->product))
            ->will($this->returnValue([]));

        $this->typeHandler = $this->getMockBuilder(\Magento\Downloadable\Model\Product\TypeHandler\TypeHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->getMockForAbstractClass();

        $this->target = $objectHelper->getObject(
            \Magento\Downloadable\Model\Product\Type::class,
            [
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDb,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'productFactory' => $productFactoryMock,
                'sampleResFactory' => $sampleResFactory,
                'linkResource' => $linkResource,
                'linksFactory' => $linksFactory,
                'samplesFactory' => $samplesFactory,
                'sampleFactory' => $sampleFactory,
                'linkFactory' => $linkFactory,
                'eavConfig' => $eavConfigMock,
                'typeHandler' => $this->typeHandler,
                'serializer' => $this->serializer
            ]
        );
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->target->hasWeight(), 'This product has weight, but it should not');
    }

    public function testBeforeSave()
    {
        $this->target->beforeSave($this->product);
    }

    public function testHasLinks()
    {
        $this->product->expects($this->exactly(2))
            ->method('getDownloadableLinks')
            ->willReturn(['link1', 'link2']);
        $this->assertTrue($this->target->hasLinks($this->product));
    }
}
