<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:disable Magento2.Security.Superglobal
 */
class MultiStoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheridoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    #[
        ConfigFixture('system/smtp/transport', 'smtp', 'store'),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(
            Customer::class,
            [
                'store_id' => '$store2.id$',
                'website_id' => '$website2.id$',
                'addresses' => [[]]
            ],
            as: 'customer1'
        ),
        DataFixture(WebsiteFixture::class, as: 'website3'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website3.id$'], 'store_group3'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group3.id$'], 'store3'),
        DataFixture(
            Customer::class,
            [
                'store_id' => '$store3.id$',
                'website_id' => '$website3.id$',
                'addresses' => [[]]
            ],
            as: 'customer2'
        ),
    ]
    public function testStoreSpecificEmailInFromHeader()
    {
        $customerOne = $this->fixtures->get('customer1');
        $storeOne = $this->fixtures->get('store2');
        $customerOneData = [
            'email' => $customerOne->getEmail(),
            'storeId' => $storeOne->getData('store_id'),
            'storeEmail' => 'store_one@example.com'
        ];

        $this->subscribeNewsLetterAndAssertFromHeader($customerOneData);

        $customerTwo = $this->fixtures->get('customer2');
        $storeTwo = $this->fixtures->get('store3');
        $customerTwoData = [
            'email' => $customerTwo->getEmail(),
            'storeId' => $storeTwo->getData('store_id'),
            'storeEmail' => 'store_two@example.com'
        ];

        $this->subscribeNewsLetterAndAssertFromHeader($customerTwoData);
    }

    /**
     * @param $customerData
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    private function subscribeNewsLetterAndAssertFromHeader(
        $customerData
    ) :void {
        /** @var Subscriber $subscriber */
        $subscriber = $this->objectManager->create(Subscriber::class);
        $subscriber->subscribe($customerData['email']);
        $subscriber->confirm($subscriber->getSubscriberConfirmCode());

        /** @var TransportBuilder $transportBuilder */
        $transportBuilder = $this->objectManager->get(TransportBuilder::class);
        $transport = $transportBuilder->setTemplateIdentifier('newsletter_subscription_confirm_email_template')
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => (int) $customerData['storeId']
                ]
            )
            ->setFromByScope(
                [
                    'email' => $customerData['storeEmail'],
                    'name' => 'Store Email Name'
                ],
                (int) $customerData['storeId']
            )
            ->setTemplateVars(
                [
                    'subscriber_data' => [
                        'confirmation_link' => $subscriber->getConfirmationLink(),
                    ],
                ]
            )
            ->addTo($customerData['email'])
            ->getTransport();
        $transport->sendMessage();
        $headers = $transport->getMessage()->getHeaders();
        $sendMessage = $transport->getMessage();
        $this->assertNotNull($sendMessage);
        $this->assertStringContainsString($customerData['storeEmail'], $headers['From']);
    }
}
