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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class InvoiceAddCommentTest
 */
class InvoiceAddCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceAddComment
     */
    protected $invoiceAddComment;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\CommentConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentConverterMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataModelMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->commentConverterMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice\CommentConverter',
            ['getModel'],
            [],
            '',
            false
        );
        $this->dataModelMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice\Comment',
            ['save', '__wakeup'],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Comment',
            [],
            [],
            '',
            false
        );
        $this->invoiceAddComment = new InvoiceAddComment($this->commentConverterMock);
    }

    /**
     * Test invoice add comment service
     */
    public function testInvoke()
    {
        $this->commentConverterMock->expects($this->once())
            ->method('getModel')
            ->with($this->equalTo($this->dataObjectMock))
            ->will($this->returnValue($this->dataModelMock));
        $this->dataModelMock->expects($this->once())
            ->method('save');
        $this->assertTrue($this->invoiceAddComment->invoke($this->dataObjectMock));
    }
}
