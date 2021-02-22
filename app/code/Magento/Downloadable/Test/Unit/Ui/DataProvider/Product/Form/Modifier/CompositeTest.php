<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Composite;
use Magento\Ui\DataProvider\Modifier\ModifierFactory;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Catalog\Model\Product\Type as CatalogType;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ModifierFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $modifierFactoryMock;

    /**
     * @var LocatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $locatorMock;

    /**
     * @var ProductInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var Composite
     */
    protected $composite;

    /**
     * @var ModifierInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $modifierMock;

    /**
     * @var array
     */
    protected $modifiers = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->modifiers = ['someClass' => 'namespase\SomeClass'];
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->modifierFactoryMock = $this->createMock(ModifierFactory::class);
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->composite = $this->objectManagerHelper->getObject(
            Composite::class,
            [
                'modifierFactory' => $this->modifierFactoryMock,
                'locator' => $this->locatorMock,
                'modifiers' => $this->modifiers
            ]
        );
    }

    /**
     * @return void
     */
    public function testModifyDataCanNotShowDownloadablePanel()
    {
        $this->modifierFactoryMock->expects($this->never())
            ->method('create');
        $this->canShowDownloadablePanel('someProductType');
        $this->assertEquals([], $this->composite->modifyData([]));
    }

    /**
     * @return void
     */
    public function testModifyMetaCanNotShowDownloadablePanel()
    {
        $this->modifierFactoryMock->expects($this->never())
            ->method('create');
        $this->canShowDownloadablePanel('someProductType');
        $this->assertEquals([], $this->composite->modifyMeta([]));
    }

    /**
     * @param string $typeId
     * @return void
     * @dataProvider productTypesDataProvider
     */
    public function testModifyData($typeId)
    {
        $modifiedData = ['someData'];
        $this->initModifiers();
        $this->canShowDownloadablePanel($typeId);
        $this->modifierMock->expects($this->once())
            ->method('modifyData')
            ->willReturn($modifiedData);
        $this->assertEquals($modifiedData, $this->composite->modifyData([]));
    }

    /**
     * @param string $typeId
     * @return void
     * @dataProvider productTypesDataProvider
     */
    public function testModifyMeta($typeId)
    {
        $modifiedMeta = ['someMeta'];
        $this->initModifiers();
        $this->canShowDownloadablePanel($typeId);
        $this->modifierMock->expects($this->once())
            ->method('modifyMeta')
            ->willReturn($modifiedMeta);
        $this->assertEquals($modifiedMeta, $this->composite->modifyMeta([]));
    }

    /**
     * @return array
     */
    public function productTypesDataProvider()
    {
        return [
            ['typeId' => DownloadableType::TYPE_DOWNLOADABLE],
            ['typeId' => CatalogType::TYPE_SIMPLE],
            ['typeId' => CatalogType::TYPE_VIRTUAL],
        ];
    }

    /**
     * @param string $typeId
     * @return void
     */
    protected function canShowDownloadablePanel($typeId)
    {
        $this->locatorMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($typeId);
    }

    /**
     * @return void
     */
    protected function initModifiers()
    {
        $this->modifierMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['modifyData', 'modifyMeta'])
            ->getMock();
        $this->modifierFactoryMock->expects($this->once())
            ->method('create')
            ->with('namespase\SomeClass')
            ->willReturn($this->modifierMock);
    }
}
