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
