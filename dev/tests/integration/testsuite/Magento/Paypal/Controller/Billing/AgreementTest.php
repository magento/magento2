<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Billing;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Paypal\Controller\Billing\Agreement\ReturnWizard;
use Magento\Paypal\Model\Express;
use Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test class for \Magento\Paypal\Controller\Billing\Agreement
 */
class AgreementTest extends AbstractController
{
    /**
     * Test billing agreement record creation in Magento DB.
     *
     * All mocking effort is aimed to disable remote call for billing agreement creation in the external system.
     * Request parameters and current customer are emulated as well.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testReturnWizardAction(): void
    {
        $paymentMethod = "paypal_express";
        $token = "token_value";
        $referenceId = "Reference-id-1";

        $objectManager = Bootstrap::getObjectManager();

        /** Mock Request */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class, [], '', false);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['payment_method', null, $paymentMethod],
                    ['token', null, $token]
                ]
            );

        /**
         * Disable billing agreement placement using calls to remote system
         * in \Magento\Paypal\Model\Billing\Agreement::place()
         */
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $paymentMethodMock = $this->getMockBuilder(Express::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['getTitle', 'setStore'])
            ->addMethods(['placeBillingAgreement'])
            ->getMock();
        $paymentMethodMock->expects($this->any())->method('placeBillingAgreement')->willReturnSelf();
        $paymentMethodMock->expects($this->any())->method('getTitle')->willReturn($paymentMethod);

        $paymentHelperMock = $this->createPartialMock(Data::class, ['getMethodInstance']);
        $paymentHelperMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($paymentMethodMock);
        $billingAgreement = $objectManager->create(
            \Magento\Paypal\Model\Billing\Agreement::class,
            ['paymentData' => $paymentHelperMock]
        );
        /** Reference ID is normally set by placeBillingAgreement() and is an agreement ID in the external system. */
        $billingAgreement->setBillingAgreementId($referenceId);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Paypal\Model\Billing\Agreement::class, [])
            ->willReturn($billingAgreement);
        $storeManager = $objectManager->get(StoreManager::class);
        $customerSession = $objectManager->get(Session::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [StoreManager::class, $storeManager],
                    [Session::class, $customerSession],
                ]
            );
        $contextMock = $objectManager->create(
            Context::class,
            [
                'objectManager' => $objectManagerMock,
                'request' => $requestMock
            ]
        );
        /** @var \Magento\Paypal\Controller\Billing\Agreement $billingAgreementController */
        $billingAgreementController = $objectManager->create(
            ReturnWizard::class,
            ['context' => $contextMock]
        );

        /** Initialize current customer */
        /** @var Session $customerSession */
        $customerSession = $objectManager->get(Session::class);
        $fixtureCustomerId = 1;
        $customerSession->setCustomerId($fixtureCustomerId);

        /** Execute SUT */
        $billingAgreementController->execute();

        /** Ensure that billing agreement record was created in the DB */
        /** @var Collection $billingAgreementCollection */
        $billingAgreementCollection = $objectManager->create(
            Collection::class
        );
        /** @var \Magento\Paypal\Model\Billing\Agreement $createdBillingAgreement */
        $createdBillingAgreement = $billingAgreementCollection->getLastItem();
        $this->assertEquals($fixtureCustomerId, $createdBillingAgreement->getCustomerId(), "Customer ID is invalid.");
        $this->assertEquals($referenceId, $createdBillingAgreement->getReferenceId(), "Reference ID is invalid.");
        $this->assertEquals($paymentMethod, $createdBillingAgreement->getMethodCode(), "Method code is invalid.");
    }
}
