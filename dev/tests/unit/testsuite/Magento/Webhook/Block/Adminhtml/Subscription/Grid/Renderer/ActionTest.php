<?php
/**
 * \Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer\Action
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderWrongType()
    {
        $context = $this->getMockBuilder('Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $gridRenderer = new \Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer\Action($context);
        $row = $this->getMockBuilder('Magento\Object')
            ->disableOriginalConstructor()
            ->getMock();

        $renderedRow = $gridRenderer->render($row);

        $this->assertEquals('', $renderedRow);
    }

    /**
     * @dataProvider renderDataProvider
     * @param int $status
     * @param string $contains
     */
    public function testRender($status, $contains)
    {
        $urlBuilder = $this->getMock('Magento\Core\Model\Url', array('getUrl'), array(), '', false);
        $urlBuilder->expects($this->any())
            ->method('getUrl')
            ->will($this->returnArgument(0));
        $translator = $this->getMock('Magento\Core\Model\Translate', array('translate'), array(), '', false);
        $context = $this->getMockBuilder('Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));
        $context->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($translator));
        $gridRenderer = new \Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer\Action($context);
        $row = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->disableOriginalConstructor()
            ->getMock();
        $row->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue($status));
        $row->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(42));

        $renderedRow = $gridRenderer->render($row);

        $this->assertFalse(false === strpos($renderedRow, '<a href="'), $renderedRow);
        $this->assertFalse(false === strpos($renderedRow, $contains), $renderedRow);
        $this->assertFalse(false === strpos($renderedRow, '</a>'), $renderedRow);
    }

    /**
     * Data provider for our testRender()
     *
     * @return array
     */
    public function renderDataProvider()
    {
        return array(
            array(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE, 'revoke'),
            array(\Magento\Webhook\Model\Subscription::STATUS_REVOKED, 'activate'),
            array(\Magento\Webhook\Model\Subscription::STATUS_INACTIVE, 'activate'),
        );
    }
}
