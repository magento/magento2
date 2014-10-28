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

namespace Magento\Paypal\Model;

class PayflowproTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Paypal\Model\Payflowpro
     */
    protected $_model;

    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    protected $_httpClientMock;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $httpClientFactoryMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClientFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpClientMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClient')->setMethods([])
            ->disableOriginalConstructor()->getMock();
        $this->_httpClientMock->expects($this->any())->method('setUri')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setConfig')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setMethod')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setParameterPost')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setHeaders')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setUrlEncodeBody')->will($this->returnSelf());

        $httpClientFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($this->_httpClientMock));

        $this->_model = $this->_objectManager->create(
            'Magento\Paypal\Model\Payflowpro',
            ['httpClientFactory' => $httpClientFactoryMock]
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_paid_with_payflowpro.php
     */
    public function testReviewPaymentNullResponce()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');

        $this->_httpClientMock->expects($this->any())->method('request')
            ->will($this->returnValue(new \Magento\Framework\Object(['body' => 'RESULTval=12&val2=34'])));
        $expectedResult = ['resultval' => '12', 'val2' => '34', 'result_code' => null, 'respmsg' => null];

        $this->assertEquals($expectedResult, $this->_model->acceptPayment($order->getPayment()));
    }
}
