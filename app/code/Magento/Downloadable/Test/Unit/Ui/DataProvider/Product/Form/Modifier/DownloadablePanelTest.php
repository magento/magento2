<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Api\Data\ProductAttributeInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\DownloadablePanel;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadablePanelTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var DownloadablePanel
     */
    protected $downloadablePanel;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->downloadablePanel = $this->objectManagerHelper->getObject(
            DownloadablePanel::class,
            [
                'locator' => $this->locatorMock,
                'arrayManager' => $this->arrayManagerMock
            ]
        );
    }

    /**
     * @param string $typeId
     * @param string $isDownloadable
     * @return void
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyData($typeId, $isDownloadable)
    {
        $productId = 1;
        $this->locatorMock->expects(static::once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects(static::once())
            ->method('getId')
            ->willReturn($productId);
        $this->productMock->expects(static::once())
            ->method('getTypeId')
            ->willReturn($typeId);
        $resultData = [
            $productId => [
                ProductAttributeInterface::CODE_IS_DOWNLOADABLE => $isDownloadable
            ]
        ];

        $this->assertEquals($resultData, $this->downloadablePanel->modifyData([]));
    }

    /**
     * @return array
     */
    public static function modifyDataDataProvider()
    {
        return [
            ['typeId' => Type::TYPE_DOWNLOADABLE, 'isDownloadable' => '1'],
            ['typeId' => 'someType', 'isDownloadable' => '0'],
        ];
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $this->locatorMock->expects(static::once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects(static::any())
            ->method('getTypeId');
        $this->arrayManagerMock->expects(static::exactly(3))
            ->method('set')
            ->willReturn([]);

        $this->assertEquals([], $this->downloadablePanel->modifyMeta([]));
    }
}
