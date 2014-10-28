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

namespace Magento\Paypal\Controller\Billing;

/**
 * Test class for \Magento\Paypal\Controller\Billing\Agreement
 */
class AgreementTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test billing agreement record creation in Magento DB.
     *
     * All mocking effort is aimed to disable remote call for billing agreement creation in the external system.
     * Request parameters and current customer are emulated as well.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     */
    public function testReturnWizardAction()
    {
        $paymentMethod = "paypal_express";
        $token = "token_value";
        $referenceId = "Reference-id-1";

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** Mock Request */
        $requestMock = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface', [], '', false);
        $requestMock
            ->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['payment_method', null, $paymentMethod],
                        ['token', null, $token]
                    ]
                )
            );

        /**
         * Disable billing agreement placement using calls to remote system
         * in \Magento\Paypal\Model\Billing\Agreement::place()
         */
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManager', [], '', false);
        $paymentMethodMock = $this->getMock(
            'Magento\Paypal\Model\Express',
            ['getTitle', 'setStore', 'placeBillingAgreement'],
            [],
            '',
            false
        );
        $paymentMethodMock->expects($this->any())->method('placeBillingAgreement')->will($this->returnSelf());
        $paymentMethodMock->expects($this->any())->method('getTitle')->will($this->returnValue($paymentMethod));

        $paymentHelperMock = $this->getMock('Magento\Payment\Helper\Data', ['getMethodInstance'], [], '', false);
        $paymentHelperMock
            ->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($paymentMethodMock));
        $billingAgreement = $objectManager->create(
            'Magento\Paypal\Model\Billing\Agreement',
            ['paymentData' => $paymentHelperMock]
        );
        /** Reference ID is normally set by placeBillingAgreement() and is an agreement ID in the external system. */
        $billingAgreement->setBillingAgreementId($referenceId);
        $objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Paypal\Model\Billing\Agreement', [])
            ->will($this->returnValue($billingAgreement));
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManager');
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\Store\Model\StoreManager', $storeManager],
                        ['Magento\Customer\Model\Session', $customerSession]
                    ]
                )
            );
        $contextMock = $objectManager->create(
            'Magento\Framework\App\Action\Context',
            [
                'objectManager' => $objectManagerMock,
                'request' => $requestMock
            ]
        );
        /** @var \Magento\Paypal\Controller\Billing\Agreement $billingAgreementController */
        $billingAgreementController = $objectManager->create(
            'Magento\Paypal\Controller\Billing\Agreement\ReturnWizard',
            ['context' => $contextMock]
        );

        /** Initialize current customer */
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $fixtureCustomerId = 1;
        $customerSession->setCustomerId($fixtureCustomerId);

        /** Execute SUT */
        $billingAgreementController->execute();

        /** Ensure that billing agreement record was created in the DB */
        /** @var \Magento\Paypal\Model\Resource\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = $objectManager->create(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection'
        );
        /** @var \Magento\Paypal\Model\Billing\Agreement $createdBillingAgreement */
        $createdBillingAgreement = $billingAgreementCollection->getLastItem();
        $this->assertEquals($fixtureCustomerId, $createdBillingAgreement->getCustomerId(), "Customer ID is invalid.");
        $this->assertEquals($referenceId, $createdBillingAgreement->getReferenceId(), "Reference ID is invalid.");
        $this->assertEquals($paymentMethod, $createdBillingAgreement->getMethodCode(), "Method code is invalid.");
    }
}
