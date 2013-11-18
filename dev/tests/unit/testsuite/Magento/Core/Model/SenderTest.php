<?php
/**
 * Unit test for \Magento\Core\Model\Sender
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\Sender
 */
namespace Magento\Core\Model;

class SenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Sender
     */
    protected $_model;

    /** @var \Magento\Core\Model\Email\Template\Mailer|PHPUnit_Framework_MockObject_MockObject */
    protected $_mailerMock;

    /** @var \Magento\Core\Model\Store|PHPUnit_Framework_MockObject_MockObject */
    protected $_storeMock;

    /** @var \Magento\Core\Model\StoreManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManagerMock;

    /** @var \Magento\Core\Model\Email\Info|PHPUnit_Framework_MockObject_MockObject */
    protected $_emailInfoMock;

    /**
     * Set required values
     */
    protected function setUp()
    {
        $this->_mailerMock = $this->getMockBuilder('Magento\Core\Model\Email\Template\Mailer')
            ->disableOriginalConstructor()
            ->setMethods(array('addEmailInfo', 'setSender', 'setStoreId', 'setTemplateId', 'setTemplateParams', 'send'))
            ->getMock();
        $this->_storeMock = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getConfig', '__sleep', '__wakeup'))
            ->getMock();
        $this->_storeManagerMock = $this->getMockBuilder('Magento\Core\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('getStore'))
            ->getMockForAbstractClass();
        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->_storeMock));
        $this->_emailInfoMock = $this->getMockBuilder('Magento\Core\Model\Email\Info')
            ->disableOriginalConstructor()
            ->setMethods(array('addTo'))
            ->getMock();

        $this->_model = new \Magento\Core\Model\Sender($this->_mailerMock, $this->_emailInfoMock,
            $this->_storeManagerMock);
    }

    public function testSend()
    {
        $email = 'test@example.com';
        $name = 'test';
        $template = 'letter_template_xml_path';
        $sender = 'sender_template_xml_path';
        $params = array('param1');
        $storeId = 1;

        $this->_storeMock->expects($this->once())->method('load')->with($this->equalTo($storeId));
        $this->_storeMock->setStoreId($storeId);

        $this->_storeMock->expects($this->at(1))
            ->method('getConfig')
            ->with($this->equalTo($sender), $this->equalTo($storeId))
            ->will($this->returnValue($sender)
        );
        $this->_storeMock->expects($this->at(2))
            ->method('getConfig')
            ->with($this->equalTo($template), $this->equalTo($storeId))
            ->will($this->returnValue($template)
        );

        $this->_mailerMock->expects($this->once())->method('addEmailInfo')->with($this->equalTo($this->_emailInfoMock));
        $this->_mailerMock->expects($this->once())->method('setSender')->with($this->equalTo($sender));
        $this->_mailerMock->expects($this->once())->method('setStoreId')->with($this->equalTo($storeId));
        $this->_mailerMock->expects($this->once())->method('setTemplateId')->with($this->equalTo($template));
        $this->_mailerMock->expects($this->once())->method('setTemplateParams')->with($this->equalTo($params));
        $this->_mailerMock->expects($this->once())->method('send');

        $this->_emailInfoMock->expects($this->once())
            ->method('addTo')
            ->with($this->equalTo($email), $this->equalTo($name));

        $this->_model->send($email, $name, $template, $sender, $params, $storeId);
    }
}
