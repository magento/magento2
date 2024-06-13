<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as ShipmentType;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePrice;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\Exception;
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
     * @var ProductTestHelper
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
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->arrayManagerMock->method('get')->willReturn([]);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->shipmentType = $this->createMock(ShipmentType::class);
        /** @var ProductInterface $productMock */
        $this->productMock = new ProductTestHelper();
        $this->productMock->setId(true)->setStoreId(0);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->locatorMock->method('getProduct')
            ->willReturn($this->productMock);

        $this->bundlePanelModel = $this->objectManager->getObject(
            BundlePanel::class,
            [
                'locator' => $this->locatorMock,
                'urlBuilder' => $this->urlBuilder,
                'shipmentType' => $this->shipmentType,
                'arrayManager' => $this->arrayManagerMock
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
     */
    #[DataProvider('getDataModifyMeta')]
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
                    ]
                ]
            );
        $this->arrayManagerMock->method('merge')
            ->willReturn([]);
        $this->arrayManagerMock->method('remove')
            ->willReturn([]);
        $this->arrayManagerMock->method('set')
            ->willReturn([]);

        $metaArgument = [
            $shipmentTypePath . BundlePanel::META_CONFIG_PATH,
            [],
            [
                'dataScope' => $dataScope,
                'validation' => [
                    'required-entry' => false
                ]
            ]
        ];
        $this->arrayManagerMock
            ->method('merge')
            ->willReturnCallback(function ($arg1) use ($metaArgument) {
                if (is_null($arg1) || $arg1 == $metaArgument) {
                    return null;
                }
            });
        $this->bundlePanelModel->modifyMeta($sourceMeta);
    }

    /**
     * Data provider for modify meta test
     *
     * @return string[][]
     */
    public static function getDataModifyMeta(): array
    {
        return [
            [
                'bundle-items/children',
                'data.product.shipment_type'
            ],
            [
                'someAttrGroup/children',
                'shipment_type'
            ]
        ];
    }
}
