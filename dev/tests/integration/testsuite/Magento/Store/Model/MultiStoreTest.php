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
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;

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
        DbIsolation(false),
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
    public function testStoreSpecificEmailInFromHeader() :void
    {
        $customerOne = $this->fixtures->get('customer1');
        $storeOne = $this->fixtures->get('store2');
        $customerOneData = [
            'email' => $customerOne->getDataByKey('email'),
            'storeId' => $storeOne->getData('store_id'),
            'storeEmail' => 'store_one@example.com'
        ];

        $this->subscribeNewsLetterAndAssertFromHeader($customerOneData);

        $customerTwo = $this->fixtures->get('customer2');
        $storeTwo = $this->fixtures->get('store3');
        $customerTwoData = [
            'email' => $customerTwo->getDataByKey('email'),
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

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $transportBuilderMock->setTemplateIdentifier(
            'customer_password_reset_password_template'
        )->setTemplateVars([
            'subscriber_data' => [
                'confirmation_link' => $subscriber->getConfirmationLink(),
            ],
        ])->setTemplateOptions([
            'area' => Area::AREA_FRONTEND,
            'store' => (int) $customerData['storeId']
        ])
        ->setFromByScope(
            [
                'email' => $customerData['storeEmail'],
                'name' => 'Store Email Name'
            ],
            (int) $customerData['storeId']
        )
        ->addTo($customerData['email'])
        ->getTransport();

        $headers = $transportBuilderMock->getSentMessage()->getHeaders();

        $this->assertNotNull($transportBuilderMock->getSentMessage());
        $this->assertStringContainsString($customerData['storeEmail'], $headers['From']);
    }
}
