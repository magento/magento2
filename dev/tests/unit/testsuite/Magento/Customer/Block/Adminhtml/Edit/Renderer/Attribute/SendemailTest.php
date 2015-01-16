<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class SendemailTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Sendemail */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Template\Context')
            ->setMethods(['getStoreManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testIsSingleStoreMode()
    {
        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', ['isSingleStoreMode'], [], '', false);
        $storeManagerMock->expects($this->any())
            ->method('isSingleStoreMode')
            ->will($this->returnValue(true));
        $this->contextMock->expects($this->any())
            ->method('getStoreManager')
            ->will($this->returnValue($storeManagerMock));

        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Sendemail',
            [
                'context' => $this->contextMock
            ]
        );

        $this->assertTrue($this->block->isSingleStoreMode());
    }

    public function testGetFormHtmlId()
    {
        $formMock = $this->getMock('Magento\Framework\Data\Form', ['getHtmlIdPrefix'], [], '', false);
        $formMock->expects($this->once())->method('getHtmlIdPrefix')->will($this->returnValue('account_form'));
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Sendemail',
            [
                'context' => $this->contextMock,
                'data' => ['form' => $formMock]
            ]
        );

        $this->assertEquals('account_form', $this->block->getFormHtmlId());
    }
}
