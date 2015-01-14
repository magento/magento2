<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class DownloadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable
     */
    protected $downloadablePlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

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
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['setDownloadableData', '__wakeup'],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );
        $this->downloadablePlugin =
            new \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable(
                $this->requestMock
            );
    }

    public function testAfterInitializeIfDownloadableExist()
    {
        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'downloadable'
        )->will(
            $this->returnValue('downloadable')
        );
        $this->productMock->expects($this->once())->method('setDownloadableData')->with('downloadable');
        $this->downloadablePlugin->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeIfDownloadableNotExist()
    {
        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'downloadable'
        )->will(
            $this->returnValue(false)
        );
        $this->productMock->expects($this->never())->method('setDownloadableData');
        $this->downloadablePlugin->afterInitialize($this->subjectMock, $this->productMock);
    }
}
