<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as ShipmentType;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePrice;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for bundle panel
 */
class BundlePanelTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var ShipmentType|MockObject
     */
    private $shipmentType;

    /**
     * @var LocatorInterface|MockObject
     */
    private $locatorMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var ArrayManager|MockObject
     */
    private $arrayManagerMock;

    /**
     * @var BundlePanel
     */
    private $bundlePanelModel;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayManagerMock->expects($this->any())
            ->method('get')
            ->willReturn([]);
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->shipmentType = $this->getMockBuilder(ShipmentType::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $this->productMock->method('getId')
            ->willReturn(true);
        $this->productMock->method('getStoreId')
            ->willReturn(0);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->onlyMethods(['getProduct'])
            ->getMockForAbstractClass();
        $this->locatorMock->method('getProduct')
            ->willReturn($this->productMock);

        $this->bundlePanelModel = $this->objectManager->getObject(
            BundlePanel::class,
            [
                'locator' => $this->locatorMock,
                'urlBuilder' => $this->urlBuilder,
                'shipmentType' => $this->shipmentType,
                'arrayManager' => $this->arrayManagerMock,
            ]
        );
    }

    /**
     * Test for modify meta
     *
     * @param string $shipmentTypePath
     * @param string $dataScope
     *
     * @return void
     * @dataProvider getDataModifyMeta
     */
    public function testModifyMeta(string $shipmentTypePath, string $dataScope): void
    {
        $sourceMeta = [
            'bundle-items' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => []
                ]
            ]
        ];
        $this->arrayManagerMock->method('findPath')
            ->willReturnMap(
                [
                    [
                        BundlePanel::CODE_SHIPMENT_TYPE,
                        [],
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $shipmentTypePath
                    ],
                ]
            );
        $this->arrayManagerMock->method('merge')
            ->willReturn([]);
        $this->arrayManagerMock->method('remove')
            ->willReturn([]);
        $this->arrayManagerMock->method('set')
            ->willReturn([]);
        $this->arrayManagerMock->expects($this->at(12))
            ->method('merge')
            ->with(
                $shipmentTypePath . BundlePanel::META_CONFIG_PATH,
                [],
                [
                    'dataScope' => $dataScope,
                    'validation' => [
                        'required-entry' => false
                    ]
                ]
            );
        $this->bundlePanelModel->modifyMeta($sourceMeta);
    }

    /**
     * Data provider for modify meta test
     *
     * @return string[][]
     */
    public function getDataModifyMeta(): array
    {
        return [
            [
                'bundle-items/children',
                'data.product.shipment_type'
            ],
            [
                'someAttrGroup/children',
                'shipment_type'
            ],
        ];
    }
}
