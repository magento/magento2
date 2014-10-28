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

class VoidTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Paypal/_files/order_payflowpro.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     */
    public function testPayflowProVoid()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $eventManager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
        $moduleList = $objectManager->get('Magento\Framework\Module\ModuleListInterface');
        $paymentData = $objectManager->get('Magento\Payment\Helper\Data');
        $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $logger = $objectManager->get('Magento\Framework\Logger');
        $logAdapterFactory = $objectManager->get('Magento\Framework\Logger\AdapterFactory');
        $localeDate = $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $centinelService = $objectManager->get('Magento\Centinel\Model\Service');
        $storeManager = $objectManager->get('Magento\Framework\StoreManagerInterface');
        $configFactory = $objectManager->get('Magento\Paypal\Model\ConfigFactory');
        $mathRandom = $objectManager->get('Magento\Framework\Math\Random');
        $httpClientFactoryMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClientFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $payment = $order->getPayment();

        /** @var \Magento\Paypal\Model\Payflowpro $instance */
        $instance = $this->getMock(
            'Magento\Paypal\Model\Payflowpro',
            array('_postRequest'),
            array(
                $eventManager,
                $paymentData,
                $scopeConfig,
                $logAdapterFactory,
                $logger,
                $moduleList,
                $localeDate,
                $centinelService,
                $storeManager,
                $configFactory,
                $mathRandom,
                $httpClientFactoryMock
            )
        );

        $response = new \Magento\Framework\Object(
            array(
                'result' => '0',
                'pnref' => 'V19A3D27B61E',
                'respmsg' => 'Approved',
                'authcode' => '510PNI',
                'hostcode' => 'A',
                'request_id' => 'f930d3dc6824c1f7230c5529dc37ae5e',
                'result_code' => '0'
            )
        );

        $instance->expects($this->any())->method('_postRequest')->will($this->returnValue($response));

        $payment->setMethodInstance($instance);
        $payment->void(new \Magento\Framework\Object());
        $order->save();

        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $this->assertFalse($order->canVoidPayment());
    }
}
