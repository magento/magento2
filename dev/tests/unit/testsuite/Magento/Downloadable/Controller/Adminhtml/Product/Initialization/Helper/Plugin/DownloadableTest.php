<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('setDownloadableData', '__wakeup'),
            array(),
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            array(),
            array(),
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
