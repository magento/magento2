<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Downloadable\Api\Data\ProductAttributeInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\DownloadablePanel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Ui\Component\Form;

/**
 * Class DownloadablePanelTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadablePanelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $locatorMock;

    /**
     * @var ArrayManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var DownloadablePanel
     */
    protected $downloadablePanel;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productMock = $this->createMock(ProductInterface::class);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
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
    public function modifyDataDataProvider()
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
