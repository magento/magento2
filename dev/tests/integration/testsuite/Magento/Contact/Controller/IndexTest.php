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
namespace Magento\Contact\Controller;

/**
 * Contact index controller test
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testPostAction()
    {
        $params = [
            'name' => 'customer name',
            'comment' => 'comment',
            'email' => 'user@example.com',
            'hideit' => ''
        ];
        $this->getRequest()->setPost($params);
        $transportBuilderMock = $this->getMock('Magento\Framework\Mail\Template\TransportBuilder', [], [], '', false);
        $transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($this->equalTo('contact_email_email_template'))
            ->will($this->returnSelf());
        $transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with(
                $this->equalTo(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => 1
                    ]
                )
            )
            ->will($this->returnSelf());
        $transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->will($this->returnSelf());
        $transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with($this->equalTo('custom2'))
            ->will($this->returnSelf());
        $transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->with($this->equalTo('hello@example.com'))
            ->will($this->returnSelf());
        $transportBuilderMock->expects($this->once())
            ->method('setReplyTo')
            ->with($this->equalTo($params['email']))
            ->will($this->returnSelf());

        $transportMock = $this->getMock('Magento\Framework\Mail\TransportInterface');
        $transportMock->expects($this->once())->method('sendMessage')->will($this->returnSelf());

        $transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->_objectManager->addSharedInstance(
            $transportBuilderMock,
            'Magento\Framework\Mail\Template\TransportBuilder'
        );
        $this->dispatch('contact/index/post');
        $this->assertSessionMessages(
            $this->contains(
                "Thanks for contacting us with your comments and questions. We'll respond to you very soon."
            ),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
