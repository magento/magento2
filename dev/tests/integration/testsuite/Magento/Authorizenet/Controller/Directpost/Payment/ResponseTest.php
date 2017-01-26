<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

/**
 * Class ResponseTest
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Authorizenet/_files/authorizenet_enabled_setting.php
 */
class ResponseTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Tests the controller for declines
     *
     * @param int $invoiceNum
     * @param string $hash
     * @param string $errorMsg
     * @param string[] $params
     *
     * @dataProvider responseActionAuthorizeCaptureDeclineDataProvider
     */
    public function testResponseActionAuthorizeCaptureDecline($invoiceNum, $hash, $errorMsg, $params)
    {
        $controllerName = 'directpost_payment';
        $controllerModule = 'authorizenet';
        $controllerAction = 'response';
        $params['x_invoice_num'] = $invoiceNum;
        $params['x_MD5_Hash'] = $hash;
        $this->getRequest()->setControllerName(
            $controllerName
        )->setControllerModule(
            $controllerModule
        )->setActionName(
            $controllerAction
        )->setRouteName(
            $controllerModule
        )->setRequestUri("/{$controllerModule}/{$controllerName}/{$controllerAction}")
            ->setParams($params);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Authorizenet\Controller\Directpost\Payment\Response */
        $controller = $objectManager->create(\Magento\Authorizenet\Controller\Directpost\Payment\Response::class);

        $response = $controller->execute();
        $output = $response->getLayout()->getOutput();

        $expectedString = "{$controllerModule}/{$controllerName}/redirect/x_invoice_num/{$params['x_invoice_num']}/"
            . "success/0/error_msg/{$errorMsg}/controller_action_name/{$controllerName}/";

        $this->assertContains('window.location', $output);
        $this->assertContains($expectedString, $output);
    }

    /**
     * Tests the controller for success
     *
     * @param string $hash
     * @param string[] $params
     *
     * @dataProvider responseActionAuthorizeCaptureSuccessDataProvider
     */
    public function testResponseActionAuthorizeCaptureSuccess($hash, $params)
    {
        $controllerName = 'directpost_payment';
        $controllerModule = 'authorizenet';
        $controllerAction = 'response';
        $params['x_invoice_num'] = 100000002;
        $params['x_MD5_Hash'] = $hash;
        $this->getRequest()->setControllerName(
            $controllerName
        )->setControllerModule(
            $controllerModule
        )->setActionName(
            $controllerAction
        )->setRouteName(
            $controllerModule
        )->setRequestUri("/{$controllerModule}/{$controllerName}/{$controllerAction}")
            ->setParams($params);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $directpostFactory = $this->getMockBuilder(\Magento\Authorizenet\Model\DirectpostFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $directpost = $this->getMockBuilder(\Magento\Authorizenet\Model\Directpost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directpostFactory->expects(static::once())
            ->method('create')
            ->willReturn($directpost);
        $directpost->expects(static::once())
            ->method('process');

        /** @var \Magento\Authorizenet\Controller\Directpost\Payment\Response $controller */
        $controller = $objectManager->create(
            \Magento\Authorizenet\Controller\Directpost\Payment\Response::class,
            [
                'directpostFactory' => $directpostFactory
            ]
        );

        $response = $controller->execute();
        $output = $response->getLayout()->getOutput();

        $expectedString = "{$controllerModule}/{$controllerName}/redirect/x_invoice_num/{$params['x_invoice_num']}/"
            . "success/1/controller_action_name/{$controllerName}/";

        $this->assertContains('window.location', $output);
        $this->assertContains($expectedString, $output);
    }

    /**
     * Tests the controller for created blocks used for sending emails that should not affect layout response
     *
     * @param string $hash
     * @param string[] $params
     *
     * @dataProvider responseActionAuthorizeCaptureSuccessDataProvider
     */
    public function testBlockCreationAffectingResult($hash, $params)
    {
        $controllerName = 'directpost_payment';
        $controllerModule = 'authorizenet';
        $controllerAction = 'response';
        $params['x_invoice_num'] = 100000002;
        $params['x_MD5_Hash'] = $hash;
        $this->getRequest()->setControllerName(
            $controllerName
        )->setControllerModule(
            $controllerModule
        )->setActionName(
            $controllerAction
        )->setRouteName(
            $controllerModule
        )->setRequestUri("/{$controllerModule}/{$controllerName}/{$controllerAction}")
            ->setParams($params);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $directpostFactory = $this->getMockBuilder(\Magento\Authorizenet\Model\DirectpostFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $directpost = $this->getMockBuilder(\Magento\Authorizenet\Model\Directpost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directpostFactory->expects(static::once())
            ->method('create')
            ->willReturn($directpost);
        $directpost->expects(static::once())
            ->method('process');

        /** @var \Magento\Authorizenet\Controller\Directpost\Payment\Response $controller */
        $controller = $objectManager->create(
            \Magento\Authorizenet\Controller\Directpost\Payment\Response::class,
            [
                'directpostFactory' => $directpostFactory
            ]
        );

        /** @var \Magento\Authorizenet\Block\Adminhtml\Order\View\Info\FraudDetails $block */
        $block = $objectManager->get(\Magento\Framework\View\LayoutInterface::class)
            ->createBlock(\Magento\Authorizenet\Block\Adminhtml\Order\View\Info\FraudDetails::class);
        $block->setTemplate('Magento_Payment::order/view/info/fraud_details.phtml');

        $response = $controller->execute();
        $output = $response->getLayout()->getOutput();

        $expectedString = "{$controllerModule}/{$controllerName}/redirect/x_invoice_num/{$params['x_invoice_num']}/"
            . "success/1/controller_action_name/{$controllerName}/";

        $this->assertContains('window.location', $output);
        $this->assertContains($expectedString, $output);
    }

    /**
     * @return array
     */
    public function responseActionAuthorizeCaptureDeclineDataProvider()
    {
        $postArray = [
            'x_response_code' => 1,
            'x_response_reason_code' => 1,
            'x_response_reason_text' => 'This transaction has been approved.',
            'x_avs_code' => 'Y',
            'x_auth_code' => 'G0L0XR',
            'x_trans_id' => '60016479791',
            'x_method' => 'CC',
            'x_card_type' => 'American Express',
            'x_account_number' => 'XXXX0002',
            'x_first_name' => 'Name',
            'x_last_name' => 'Surname',
            'x_company' => null,
            'x_address' => 'Address',
            'x_city' => 'Austin',
            'x_state' => 'Texas',
            'x_zip' => '78753',
            'x_country' => 'US',
            'x_phone' => '5127242323',
            'x_fax' => null,
            'x_email' => 'customer@example.com',
            'x_description' => null,
            'x_type' => 'auth_capture',
            'x_cust_id' => null,
            'x_ship_to_first_name' => null,
            'x_ship_to_last_name' => null,
            'x_ship_to_company' => null,
            'x_ship_to_address' => null,
            'x_ship_to_city' => null,
            'x_ship_to_state' => null,
            'x_ship_to_zip' => null,
            'x_ship_to_country' => null,
            'x_amount' => 100.00,
            'x_tax' => 0.00,
            'x_duty' => 0.00,
            'x_freight' => 0.00,
            'x_tax_exempt' => false,
            'x_po_num' => null,
            'x_SHA2_Hash' => null,
            'x_cvv2_resp_code' => 'P',
            'x_cavv_response' => 2,
            'x_test_request' => false,
            'controller_action_name' => 'directpost_payment',
            'is_secure' => null
        ];
        return [
            'error_hash' => [
                'invoice_num' => '1231231',
                'x_MD5_Hash' => 'F9AE81A5DA36057D1312D71C904FCCF2',
                'error_msg' => 'The%20transaction%20was%20declined%20because%20the%20'
                    . 'response%20hash%20validation%20failed.',
                'post' => $postArray
            ]
        ];
    }

    /**
     * @return array
     */
    public function responseActionAuthorizeCaptureSuccessDataProvider()
    {
        $postArray = [
            'x_response_code' => 1,
            'x_response_reason_code' => 1,
            'x_response_reason_text' => 'This transaction has been approved.',
            'x_avs_code' => 'Y',
            'x_auth_code' => 'G0L0XR',
            'x_trans_id' => '60016479791',
            'x_method' => 'CC',
            'x_card_type' => 'American Express',
            'x_account_number' => 'XXXX0002',
            'x_first_name' => 'Name',
            'x_last_name' => 'Surname',
            'x_company' => null,
            'x_address' => 'Address',
            'x_city' => 'Austin',
            'x_state' => 'Texas',
            'x_zip' => '78753',
            'x_country' => 'US',
            'x_phone' => '5127242323',
            'x_fax' => null,
            'x_email' => 'integrationtest@magento.com',
            'x_description' => null,
            'x_type' => 'auth_capture',
            'x_cust_id' => null,
            'x_ship_to_first_name' => null,
            'x_ship_to_last_name' => null,
            'x_ship_to_company' => null,
            'x_ship_to_address' => null,
            'x_ship_to_city' => null,
            'x_ship_to_state' => null,
            'x_ship_to_zip' => null,
            'x_ship_to_country' => null,
            'x_amount' => 120.15,
            'x_tax' => 0.00,
            'x_duty' => 0.00,
            'x_freight' => 0.00,
            'x_tax_exempt' => false,
            'x_po_num' => null,
            'x_SHA2_Hash' => null,
            'x_cvv2_resp_code' => 'P',
            'x_cavv_response' => 2,
            'x_test_request' => false,
            'controller_action_name' => 'directpost_payment',
            'is_secure' => null
        ];
        return [
            'success' => [
                'x_MD5_Hash' => '35DCF749F7760193FB8254886E1D1522',
                'post' => $postArray
            ],
        ];
    }
}
