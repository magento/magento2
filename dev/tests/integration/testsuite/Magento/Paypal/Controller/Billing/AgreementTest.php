<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $requestMock = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
        $requestMock
            ->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['payment_method', null, $paymentMethod],
                        ['token', null, $token],
                    ]
                )
            );

        /**
         * Disable billing agreement placement using calls to remote system
         * in \Magento\Paypal\Model\Billing\Agreement::place()
         */
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $paymentMethodMock = $this->getMock(
            \Magento\Paypal\Model\Express::class,
            ['getTitle', 'setStore', 'placeBillingAgreement'],
            [],
            '',
            false
        );
        $paymentMethodMock->expects($this->any())->method('placeBillingAgreement')->will($this->returnSelf());
        $paymentMethodMock->expects($this->any())->method('getTitle')->will($this->returnValue($paymentMethod));

        $paymentHelperMock = $this->getMock(\Magento\Payment\Helper\Data::class, ['getMethodInstance'], [], '', false);
        $paymentHelperMock
            ->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($paymentMethodMock));
        $billingAgreement = $objectManager->create(
            \Magento\Paypal\Model\Billing\Agreement::class,
            ['paymentData' => $paymentHelperMock]
        );
        /** Reference ID is normally set by placeBillingAgreement() and is an agreement ID in the external system. */
        $billingAgreement->setBillingAgreementId($referenceId);
        $objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(\Magento\Paypal\Model\Billing\Agreement::class, [])
            ->will($this->returnValue($billingAgreement));
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManager::class);
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        $objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [\Magento\Store\Model\StoreManager::class, $storeManager],
                        [\Magento\Customer\Model\Session::class, $customerSession],
                    ]
                )
            );
        $contextMock = $objectManager->create(
            \Magento\Framework\App\Action\Context::class,
            [
                'objectManager' => $objectManagerMock,
                'request' => $requestMock
            ]
        );
        /** @var \Magento\Paypal\Controller\Billing\Agreement $billingAgreementController */
        $billingAgreementController = $objectManager->create(
            \Magento\Paypal\Controller\Billing\Agreement\ReturnWizard::class,
            ['context' => $contextMock]
        );

        /** Initialize current customer */
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        $fixtureCustomerId = 1;
        $customerSession->setCustomerId($fixtureCustomerId);

        /** Execute SUT */
        $billingAgreementController->execute();

        /** Ensure that billing agreement record was created in the DB */
        /** @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = $objectManager->create(
            \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection::class
        );
        /** @var \Magento\Paypal\Model\Billing\Agreement $createdBillingAgreement */
        $createdBillingAgreement = $billingAgreementCollection->getLastItem();
        $this->assertEquals($fixtureCustomerId, $createdBillingAgreement->getCustomerId(), "Customer ID is invalid.");
        $this->assertEquals($referenceId, $createdBillingAgreement->getReferenceId(), "Reference ID is invalid.");
        $this->assertEquals($paymentMethod, $createdBillingAgreement->getMethodCode(), "Method code is invalid.");
    }
}
