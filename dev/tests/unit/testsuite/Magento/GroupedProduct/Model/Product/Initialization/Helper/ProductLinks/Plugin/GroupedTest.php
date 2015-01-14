<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin;


class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getGroupedReadonly', 'setGroupedLinkData', '__wakeup'],
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
        $this->model = new Grouped();
    }

    public function testBeforeInitializeLinksRequestDoesNotHaveGrouped()
    {
        $this->productMock->expects($this->never())->method('getGroupedReadonly');
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, []);
    }

    public function testBeforeInitializeLinksRequestHasGrouped()
    {
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setGroupedLinkData')->with(['value']);
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => 'value']);
    }

    public function testBeforeInitializeLinksProductIsReadonly()
    {
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->will($this->returnValue(true));
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => 'value']);
    }
}
