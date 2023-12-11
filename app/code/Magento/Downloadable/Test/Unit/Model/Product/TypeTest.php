<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Product\TypeHandler\TypeHandler;
use Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface;
use Magento\Downloadable\Model\ResourceModel\Link;
use Magento\Downloadable\Model\ResourceModel\Link\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory;
use Magento\Downloadable\Model\ResourceModel\SampleFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class TypeTest
 * Test for \Magento\Downloadable\Model\Product\Type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends TestCase
{
    /**
     * @var Type
     */
    private $target;

    /**
     * @var TypeHandlerInterface|MockObject
     */
    private $typeHandler;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CollectionFactory|MockObject
     */
    private $linksFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $fileStorageDb = $this->getMockBuilder(
            Database::class
        )->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->createMock(Registry::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $productFactoryMock = $this->createMock(ProductFactory::class);
        $sampleResFactory = $this->createMock(SampleFactory::class);
        $linkResource = $this->createMock(Link::class);
        $this->linksFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $samplesFactory = $this->createMock(\Magento\Downloadable\Model\ResourceModel\Sample\CollectionFactory::class);
        $sampleFactory = $this->createMock(\Magento\Downloadable\Model\SampleFactory::class);
        $linkFactory = $this->createMock(LinkFactory::class);

        $entityTypeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $resourceProductMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product::class,
            ['getEntityType']
        );
        $resourceProductMock->expects($this->any())->method('getEntityType')->willReturn($entityTypeMock);

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setConstructorArgs(['serialize', 'unserialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods([
                'getLinksPurchasedSeparately',
                'setTypeHasRequiredOptions',
                'setRequiredOptions',
                'getDownloadableData',
                'setTypeHasOptions',
                'setLinksExist',
                'getDownloadableLinks'
            ])
            ->onlyMethods([
                'getResource',
                'canAffectOptions',
                '__wakeup',
                'getCustomOption',
                'addCustomOption',
                'getEntityId'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->any())->method('getResource')->willReturn($resourceProductMock);
        $this->product->expects($this->any())->method('setTypeHasRequiredOptions')->with(true)->willReturnSelf();
        $this->product->expects($this->any())->method('setRequiredOptions')->with(true)->willReturnSelf();
        $this->product->expects($this->any())->method('setTypeHasOptions')->with(false);
        $this->product->expects($this->any())->method('setLinksExist')->with(false);
        $this->product->expects($this->any())->method('canAffectOptions')->with(true);

        $eavConfigMock = $this->createPartialMock(Config::class, ['getEntityAttributes']);
        $eavConfigMock->expects($this->any())
            ->method('getEntityAttributes')
            ->with($entityTypeMock, $this->product)
            ->willReturn([]);

        $this->typeHandler = $this->getMockBuilder(TypeHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->target = $this->objectManager->getObject(
            Type::class,
            [
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDb,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'productFactory' => $productFactoryMock,
                'sampleResFactory' => $sampleResFactory,
                'linkResource' => $linkResource,
                'linksFactory' => $this->linksFactory,
                'samplesFactory' => $samplesFactory,
                'sampleFactory' => $sampleFactory,
                'linkFactory' => $linkFactory,
                'eavConfig' => $eavConfigMock,
                'typeHandler' => $this->typeHandler,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->target->hasWeight(), 'This product has weight, but it should not');
    }

    public function testBeforeSave()
    {
        $result = $this->target->beforeSave($this->product);
        $this->assertEquals($result, $this->target);
    }

    public function testHasLinks()
    {
        $this->product->expects($this->any())->method('getLinksPurchasedSeparately')->willReturn(true);
        $this->product->expects($this->exactly(2))
            ->method('getDownloadableLinks')
            ->willReturn(['link1', 'link2']);
        $this->assertTrue($this->target->hasLinks($this->product));
    }

    public function testCheckProductBuyState()
    {
        $optionMock = $this->getMockBuilder(Option::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionMock->expects($this->any())->method('getValue')->willReturn('{}');

        $this->product->expects($this->any())
            ->method('getCustomOption')
            ->with('info_buyRequest')
            ->willReturn($optionMock);

        $this->product->expects($this->any())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(false);

        $this->product->expects($this->any())
            ->method('getEntityId')
            ->willReturn(123);

        $linksCollectionMock = $this->createPartialMock(
            Collection::class,
            ['addProductToFilter', 'getAllIds']
        );

        $linksCollectionMock->expects($this->once())
            ->method('addProductToFilter')
            ->with(123)->willReturnSelf();

        $linksCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1, 2, 3]);

        $this->linksFactory->expects($this->any())
            ->method('create')
            ->willReturn($linksCollectionMock);

        $this->product->expects($this->once())
            ->method('addCustomOption')
            ->with('info_buyRequest', '{"links":[1,2,3]}');

        $this->target->checkProductBuyState($this->product);
    }

    public function testCheckProductBuyStateException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please specify product link(s).');
        $optionMock = $this->createPartialMock(Option::class, ['getValue']);

        $optionMock->expects($this->any())->method('getValue')->willReturn('{}');

        $this->product->expects($this->any())
            ->method('getCustomOption')
            ->with('info_buyRequest')
            ->willReturn($optionMock);

        $this->product->expects($this->any())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(true);

        $this->target->checkProductBuyState($this->product);
    }
}
