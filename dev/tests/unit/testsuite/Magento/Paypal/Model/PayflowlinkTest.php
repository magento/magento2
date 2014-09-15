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

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PayflowlinkTest extends \PHPUnit_Framework_TestCase
{
    /** @var Payflowlink */
    protected $model;

    /** @var  \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject */
    protected $infoInstance;

    /** @var  \Magento\Paypal\Model\Payflow\Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $payflowRequest;

    /** @var  \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $paypalConfig;

    /** @var  \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    protected function setUp()
    {
        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeManager = $this->getMock('Magento\Framework\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $this->paypalConfig = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);
        $configFactory = $this->getMock('Magento\Paypal\Model\ConfigFactory', ['create']);
        $configFactory->expects($this->any())->method('create')->will($this->returnValue($this->paypalConfig));
        $this->payflowRequest = $this->getMock('Magento\Paypal\Model\Payflow\Request', [], [], '', false);
        $this->payflowRequest->expects($this->any())
            ->method('__call')
            ->will($this->returnCallback(function ($method) {
                if (strpos($method, 'set') === 0) {
                    return $this->payflowRequest;
                }
                return null;
            }));
        $requestFactory = $this->getMock('Magento\Paypal\Model\Payflow\RequestFactory', ['create']);
        $requestFactory->expects($this->any())->method('create')->will($this->returnValue($this->payflowRequest));
        $this->infoInstance = $this->getMock('Magento\Sales\Model\Order\Payment', [], [], '', false);

        $client = $this->getMock(
            'Magento\Framework\HTTP\ZendClient',
            [
                'setUri',
                'setConfig',
                'setMethod',
                'setParameterPost',
                'setHeaders',
                'setUrlEncodeBody',
                'request',
                'getBody'
            ],
            [],
            '',
            false
        );
        $client->expects($this->any())->method('create')->will($this->returnSelf());
        $client->expects($this->any())->method('setUri')->will($this->returnSelf());
        $client->expects($this->any())->method('setConfig')->will($this->returnSelf());
        $client->expects($this->any())->method('setMethod')->will($this->returnSelf());
        $client->expects($this->any())->method('setParameterPost')->will($this->returnSelf());
        $client->expects($this->any())->method('setHeaders')->will($this->returnSelf());
        $client->expects($this->any())->method('setUrlEncodeBody')->will($this->returnSelf());
        $client->expects($this->any())->method('request')->will($this->returnSelf());
        $client->expects($this->any())->method('getBody')->will($this->returnValue('RESULT name=value&name2=value2'));
        $clientFactory = $this->getMock('Magento\Framework\HTTP\ZendClientFactory', ['create'], [], '', false);
        $clientFactory->expects($this->any())->method('create')->will($this->returnValue($client));

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            'Magento\Paypal\Model\Payflowlink',
            [
                'storeManager' => $storeManager,
                'configFactory' => $configFactory,
                'requestFactory' => $requestFactory,
                'httpClientFactory' => $clientFactory
            ]
        );
        $this->model->setInfoInstance($this->infoInstance);
    }

    public function testInitialize()
    {
        $order = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->infoInstance->expects($this->any())->method('getOrder')->will($this->returnValue($order));
        $this->infoInstance->expects($this->any())->method('setAdditionalInformation')->will($this->returnSelf());
        $this->paypalConfig->expects($this->once())->method('getBuildNotationCode')
            ->will($this->returnValue('build notation code'));
        $this->payflowRequest->expects($this->once())->method('setData')->with('BNCODE', 'build notation code')
            ->will($this->returnSelf());
        $stateObject = new \Magento\Framework\Object();
        $this->model->initialize(\Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH, $stateObject);
    }
}
