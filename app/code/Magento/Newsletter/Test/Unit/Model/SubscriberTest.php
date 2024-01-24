<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Newsletter\Helper\Data;
use Magento\Newsletter\Model\Queue;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Model\Subscriber
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriberTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private $newsletterData;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var AccountManagementInterface|MockObject
     */
    private $customerAccountManagement;

    /**
     * @var StateInterface|MockObject
     */
    private $inlineTranslation;

    /**
     * @var SubscriberResourceModel|MockObject
     */
    private $resource;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    private $customerFactory;

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->newsletterData = $this->createMock(Data::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->transportBuilder = $this->createPartialMock(
            TransportBuilder::class,
            [
                'setTemplateIdentifier',
                'setTemplateOptions',
                'setTemplateVars',
                'setFrom',
                'setFromByScope',
                'addTo',
                'getTransport'
            ]
        );
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->customerSession = $this->createPartialMock(
            Session::class,
            [
                'isLoggedIn',
                'getCustomerDataObject',
                'getCustomerId'
            ]
        );
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->customerAccountManagement = $this->getMockForAbstractClass(AccountManagementInterface::class);
        $this->inlineTranslation = $this->getMockForAbstractClass(StateInterface::class);
        $this->resource = $this->getMockBuilder(SubscriberResourceModel::class)
            ->addMethods(
                ['loadByCustomer']
            )
            ->onlyMethods(
                ['loadByEmail', 'getIdFieldName', 'save', 'received', 'loadBySubscriberEmail', 'loadByCustomerId']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new ObjectManager($this);

        $this->customerFactory = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = $this->objectManager->getObject(
            Subscriber::class,
            [
                'newsletterData' => $this->newsletterData,
                'scopeConfig' => $this->scopeConfig,
                'transportBuilder' => $this->transportBuilder,
                'storeManager' => $this->storeManager,
                'customerSession' => $this->customerSession,
                'customerRepository' => $this->customerRepository,
                'customerAccountManagement' => $this->customerAccountManagement,
                'inlineTranslation' => $this->inlineTranslation,
                'resource' => $this->resource,
                'customerFactory' => $this->customerFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );
    }

    /**
     * Test to Load by subscriber email
     *
     * @return void
     */
    public function testLoadBySubscriberEmail(): void
    {
        $email = 'subscriber_email@example.com';
        $websiteId = 1;
        $subscriberData = ['some_filed' => 'value'];

        $this->resource->expects($this->once())
            ->method('loadBySubscriberEmail')
            ->with($email, $websiteId)
            ->willReturn($subscriberData);

        $this->assertEquals(
            $subscriberData,
            $this->subscriber->loadBySubscriberEmail($email, $websiteId)->getData()
        );
    }

    /**
     * Test to Load by customer
     *
     * @return void
     */
    public function testLoadByCustomer(): void
    {
        $customerId = 1;
        $websiteId = 1;
        $subscriberData = ['some_filed' => 'value'];

        $this->resource->expects($this->once())
            ->method('loadByCustomerId')
            ->with($customerId, $websiteId)
            ->willReturn($subscriberData);

        $this->assertEquals(
            $subscriberData,
            $this->subscriber->loadByCustomer($customerId, $websiteId)->getData()
        );
    }

    /**
     * Test to unsubscribe customer from newsletters
     */
    public function testUnsubscribe()
    {
        $this->resource->expects($this->once())->method('save')->willReturnSelf();
        $subscriberData = [
            'store_id' => 2,
            'email' => 'subscriber_email@example.com',
            'name' => 'Subscriber Name',
        ];
        $this->subscriber->setData($subscriberData);
        $this->sendEmailCheck(
            Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE,
            Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY
        );

        $this->assertEquals($this->subscriber, $this->subscriber->unsubscribe());
    }

    /**
     * Test to try unsubscribe customer from newsletters with wrong confirmation code
     */
    public function testUnsubscribeException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('This is an invalid subscription confirmation code.');
        $this->subscriber->setCode(111);
        $this->subscriber->setCheckCode(222);

        $this->subscriber->unsubscribe();
    }

    /**
     * Test to get subscriber full name
     */
    public function testGetSubscriberFullName()
    {
        $this->subscriber->setFirstname('John');
        $this->subscriber->setLastname('Doe');

        $this->assertEquals('John Doe', $this->subscriber->getSubscriberFullName());
    }

    /**
     * Test to confirm customer subscription
     */
    public function testConfirm()
    {
        $code = 111;
        $this->subscriber->setCode($code);
        $this->resource->expects($this->once())->method('save')->willReturnSelf();

        $this->assertTrue($this->subscriber->confirm($code));
    }

    /**
     * Test to doesn't confirm customer subscription
     */
    public function testConfirmWrongCode()
    {
        $code = 111;
        $this->subscriber->setCode(222);

        $this->assertFalse($this->subscriber->confirm($code));
    }

    /**
     * Test to mark receiving subscriber of queue newsletter
     */
    public function testReceived()
    {
        $queue = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('received')->with($this->subscriber, $queue)->willReturnSelf();

        $this->assertEquals($this->subscriber, $this->subscriber->received($queue));
    }

    /**
     * Test to Sends out confirmation email
     *
     * @return void
     */
    public function testSendConfirmationRequestEmail(): void
    {
        $confirmLink = 'confirm link';
        $storeId = 2;
        $subscriberData = [
            'store_id' => $storeId,
            'email' => 'subscriber_email@example.com',
            'name' => 'Subscriber Name',
        ];
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        $this->newsletterData->expects($this->once())
            ->method('getConfirmationUrl')
            ->with($this->subscriber)
            ->willReturn($confirmLink);
        $this->subscriber->setData($subscriberData);
        $this->sendEmailCheck(
            Subscriber::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
            Subscriber::XML_PATH_CONFIRM_EMAIL_IDENTITY,
            [
                'store' => $store,
                'subscriber_data' => [
                    'confirmation_link' => $confirmLink,
                ],
            ]
        );
        $this->assertEquals($this->subscriber, $this->subscriber->sendConfirmationRequestEmail());
    }

    /**
     * Test to Sends out success email
     *
     * @return void
     */
    public function testSendConfirmationSuccessEmail(): void
    {
        $subscriberData = [
            'store_id' => 2,
            'email' => 'subscriber_email@example.com',
            'name' => 'Subscriber Name',
        ];
        $this->subscriber->setData($subscriberData);
        $this->sendEmailCheck(
            Subscriber::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
            Subscriber::XML_PATH_SUCCESS_EMAIL_IDENTITY
        );
        $this->assertEquals($this->subscriber, $this->subscriber->sendConfirmationSuccessEmail());
    }

    /**
     * Check to send email
     *
     * @param string $templateConfigPath
     * @param string $identityTemplatePath
     * @return void
     */
    private function sendEmailCheck(string $templateConfigPath, string $identityTemplatePath, array $vars = []): void
    {
        $template = 'email_template';
        $identity = 'email_identity';
        $vars += ['subscriber' => $this->subscriber];

        $this->scopeConfig->method('getValue')
            ->willReturnMap(
                [
                    [$templateConfigPath, ScopeInterface::SCOPE_STORE, $this->subscriber->getStoreId(), $template],
                    [$identityTemplatePath, ScopeInterface::SCOPE_STORE, $this->subscriber->getStoreId(), $identity],
                ]
            );
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($template)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $this->subscriber->getStoreId()])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->with($vars)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setFromByScope')
            ->with($identity, $this->subscriber->getStoreId())
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($this->subscriber->getEmail(), $this->subscriber->getName())
            ->willReturnSelf();
        $transport = $this->getMockForAbstractClass(TransportInterface::class);
        $transport->expects($this->once())->method('sendMessage')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('getTransport')->willReturn($transport);
        $this->inlineTranslation->expects($this->once())->method('suspend')->willReturnSelf();
        $this->inlineTranslation->expects($this->once())->method('resume')->willReturnSelf();
    }
}
