<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Directory\Helper\Data as LocaleConfig;
use Magento\Email\Test\Fixture\FileTransport as FileTransportFixture;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Parser;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Translation\Test\Fixture\Translation as TranslationFixture;

class AsyncBulkAccountManagementTest extends WebapiAbstract
{
    private const ASYNC_BULK_RESOURCE_PATH = '/async/bulk/V1/customers';

    private const ASYNC_CONSUMER_NAME = 'async.operations.all';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $params = array_merge_recursive(
            Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );

        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $this->objectManager->create(PublisherConsumerController::class, [
            'consumers'     => [self::ASYNC_CONSUMER_NAME],
            'logFilePath'   => TESTS_TEMP_DIR . '/MessageQueueTestLog.txt',
            'appInitParams' => $params,
        ]);

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }

        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    #[
        DataFixture(
            TranslationFixture::class,
            [
                'string' => 'Welcome to %store_name.',
                'translate' => 'Bienvenue sur %store_name.',
                'locale' => 'fr_FR',
            ]
        ),
        DataFixture(FileTransportFixture::class, as: 'mail_transport_config'),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(
            StoreGroupFixture::class,
            ['website_id' => '$website2.id$', 'name' => 'Le Site de Paris'],
            'store_group2'
        ),
        DataFixture(
            StoreFixture::class,
            ['store_group_id' => '$store_group2.id$', 'code' => 'fr_store_view'],
            'store2'
        ),
        Config(LocaleConfig::XML_PATH_DEFAULT_LOCALE, 'fr_FR', 'store', 'fr_store_view'),
    ]
    public function testMailShouldBeTranslatedToStoreLanguage(): void
    {
        $this->_markTestAsRestOnly();
        $fixtures = DataFixtureStorageManager::getStorage();

        $postData = [
            [
                'customer' => [
                    'email' => 'john' . uniqid() . '@example.com',
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'website_id' => $fixtures->get('website2')->getId()
                ],
                'password' => 'J!Do&007',
            ]
        ];
        $response = $this->postAsync($postData);
        $this->assertFalse($response['errors']);
        $this->assertCount(1, $response['request_items']);

        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                function (array $data) {
                    return 1 === $this->objectManager->create(Collection::class)
                        ->addAttributeToFilter('email', ['eq' => $data[0]['customer']['email']])
                        ->getSize();
                },
                [$postData]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("Customer was not created");
        }

        $filesystem = $this->objectManager->get(Filesystem::class);
        $directory = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        // wait until a mail is sent
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                function (\Magento\Framework\Filesystem\Directory\ReadInterface $directory) {
                    return count($directory->read('')) > 0;
                },
                [$directory]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("No mail was sent");
        }

        $mailPaths = $directory->read(DirectoryList::VAR_DIR);
        $sentMails = count($mailPaths);
        $this->assertCount(1, $mailPaths, "Only 1 mail was expected to be sent, actually $sentMails were sent.");
        $mailContent = $directory->readFile('mail-transport-config.json');
        $parser = $this->objectManager->get(Parser::class);
        $message = $parser->fromString((string)$mailContent)->getSymfonyMessage()->getBody();
        $decoded_data = base64_decode($message->bodyToString());
        $parts = explode("\r\n", $decoded_data);
        $count = count($parts);
        $mergedString = base64_decode($parts[$count - 2] . $parts[$count - 1]);
        $this->assertStringContainsString(
            'Bienvenue sur Le Site de Paris.',
            $mergedString
        );
    }

    /***
     * @param array $data
     * @param string|null $storeCode
     * @return mixed
     */
    private function postAsync(array $data, ?string $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_BULK_RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $data, null, $storeCode);
    }
}
