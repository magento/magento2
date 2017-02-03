<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Initialization\Helper\ProductLinks\Plugin;

use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\Product\Type;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getGroupedReadonly', 'setGroupedLinkData', '__wakeup', 'getTypeId'],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks',
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped();
    }

    /**
     * @dataProvider productTypeDataProvider
     */
    public function testBeforeInitializeLinksRequestDoesNotHaveGrouped($productType)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($productType));
        $this->productMock->expects($this->never())->method('getGroupedReadonly');
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, []);
    }

    public function productTypeDataProvider()
    {
        return [
            [Type::TYPE_SIMPLE],
            [Type::TYPE_BUNDLE],
            [Type::TYPE_VIRTUAL]
        ];
    }

    /**
     * @dataProvider linksDataProvider
     */
    public function testBeforeInitializeLinksRequestHasGrouped($linksData)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(Grouped::TYPE_CODE));
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setGroupedLinkData')->with($linksData);
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => $linksData]);
    }

    public function linksDataProvider()
    {
        return [
            [['associated' => [5 => ['id' => '2', 'qty' => '100', 'position' => '1']]]],
            [['associated' => []]],
            [[]]
        ];
    }

    public function testBeforeInitializeLinksProductIsReadonly()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(Grouped::TYPE_CODE));
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->will($this->returnValue(true));
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => 'value']);
    }
}
