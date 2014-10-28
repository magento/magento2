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
namespace Magento\Sales\Service\V1;

/**
 * Class InvoiceWriteTest
 */
class InvoiceWriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceAddComment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceAddCommentMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceVoid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceVoidMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceEmail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceEmailMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceCapture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceCaptureMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceCreate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceCreateMock;

    /**
     * @var \Magento\Sales\Service\V1\InvoiceWrite
     */
    protected $invoiceWrite;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->invoiceAddCommentMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\InvoiceAddComment',
            ['invoke'],
            [],
            '',
            false
        );
        $this->invoiceVoidMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\InvoiceVoid',
            ['invoke'],
            [],
            '',
            false
        );
        $this->invoiceEmailMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\InvoiceEmail',
            ['invoke'],
            [],
            '',
            false
        );
        $this->invoiceCaptureMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\InvoiceCapture',
            ['invoke'],
            [],
            '',
            false
        );
        $this->invoiceCreateMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\InvoiceCreate',
            ['invoke'],
            [],
            '',
            false
        );

        $this->invoiceWrite = new InvoiceWrite(
            $this->invoiceAddCommentMock,
            $this->invoiceVoidMock,
            $this->invoiceEmailMock,
            $this->invoiceCaptureMock,
            $this->invoiceCreateMock
        );
    }

    /**
     * test invoice add comment
     */
    public function testAddComment()
    {
        $comment = $this->getMock('Magento\Sales\Service\V1\Data\Comment', [], [], '', false);
        $this->invoiceAddCommentMock->expects($this->once())
            ->method('invoke')
            ->with($comment)
            ->will($this->returnValue(true));
        $this->assertTrue($this->invoiceWrite->addComment($comment));
    }

    /**
     * test invoice void
     */
    public function testVoid()
    {
        $this->invoiceVoidMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue(true));
        $this->assertTrue($this->invoiceWrite->void(1));
    }

    /**
     * test invoice email
     */
    public function testEmail()
    {
        $this->invoiceEmailMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue(true));
        $this->assertTrue($this->invoiceWrite->email(1));
    }

    /**
     * test invoice capture
     */
    public function testCapture()
    {
        $this->invoiceCaptureMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue(true));
        $this->assertTrue($this->invoiceWrite->capture(1));
    }

    /**
     * test invoice create
     */
    public function testCreate()
    {
        $invoiceDataObject = $this->getMock('Magento\Sales\Service\V1\Data\Invoice', [], [], '', false);
        $this->invoiceCreateMock->expects($this->once())
            ->method('invoke')
            ->with($invoiceDataObject)
            ->will($this->returnValue(true));
        $this->assertTrue($this->invoiceWrite->create($invoiceDataObject));
    }
}
