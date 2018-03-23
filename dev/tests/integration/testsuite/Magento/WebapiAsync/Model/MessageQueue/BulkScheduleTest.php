<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Model\MessageQueue;

use Magento\Catalog\Api\Data\ProductInterface;
//use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
//use Magento\MessageQueue\Model\Cron\ConsumersRunner\PidConsumerManager;
//use Magento\Framework\App\DeploymentConfig\FileReader;
//use Magento\Framework\App\DeploymentConfig\Writer;
//use Magento\Framework\Config\File\ConfigFilePool;
//use Magento\Framework\ShellInterface;
//use Magento\Framework\Filesystem;
//use Magento\Framework\App\Filesystem\DirectoryList;
//use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\WebapiAsync\WebApiAsyncBaseTestCase;

/**
 * Check async request for product creation service, scheduling bulk to rabbitmq
 * running consumers and check async.opearion.add consumer
 * check if product was created by async requests
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkScheduleTest extends WebApiAsyncBaseTestCase
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const REST_RESOURCE_PATH = '/V1/products';
    const ASYNC_RESOURCE_PATH = '/async/V1/products';
    const ASYNC_CONSUMER_NAME = 'async.V1.products.POST';

    const KEY_TIER_PRICES = 'tier_prices';
    const KEY_SPECIAL_PRICE = 'special_price';
    const KEY_CATEGORY_LINKS = 'category_links';

    const BULK_UUID_KEY = 'bulk_uuid';

    protected $consumers = [
        self::ASYNC_CONSUMER_NAME
    ];

//    /**
//     * @var \Magento\Framework\ObjectManagerInterface
//     */
//    private $objectManager;
//
//    /**
//     * Consumer config provider
//     *
//     * @var ConsumerConfigInterface
//     */
//    private $consumerConfig;
//
//    /**
//     * @var PidConsumerManager
//     */
//    private $pid;
//
//    /**
//     * @var FileReader
//     */
//    private $reader;
//
//    /**
//     * @var \Magento\MessageQueue\Model\Cron\ConsumersRunner
//     */
//    private $consumersRunner;
//
//    /**
//     * @var Filesystem
//     */
//    private $filesystem;
//
//    /**
//     * @var ConfigFilePool
//     */
//    private $configFilePool;
//
//    /**
//     * @var ReinitableConfigInterface
//     */
//    private $appConfig;
//
//    /**
//     * @var ShellInterface|\PHPUnit_Framework_MockObject_MockObject
//     */
//    private $shellMock;
//
//    /**
//     * @var array
//     */
//    private $config;

//    /**
//     * @inheritdoc
//     */
//    protected function setUp()
//    {
//        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
//        $this->shellMock = $this->getMockBuilder(ShellInterface::class)
//            ->getMockForAbstractClass();
//        $this->pid = $this->objectManager->get(PidConsumerManager::class);
//        $this->consumerConfig = $this->objectManager->get(ConsumerConfigInterface::class);
//        $this->reader = $this->objectManager->get(FileReader::class);
//        $this->filesystem = $this->objectManager->get(Filesystem::class);
//        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
//        $this->appConfig = $this->objectManager->get(ReinitableConfigInterface::class);
//
//        $this->consumersRunner = $this->objectManager->create(
//            \Magento\MessageQueue\Model\Cron\ConsumersRunner::class,
//            ['shellBackground' => $this->shellMock]
//        );
//
//        $this->config = $this->loadConfig();
//
//        $this->shellMock->expects($this->any())
//            ->method('execute')
//            ->willReturnCallback(function ($command, $arguments) {
//                $command = vsprintf($command, $arguments);
//                $params =
//                    \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams();
//                $params['MAGE_DIRS']['base']['path'] = BP;
//                $params =
//                    'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
//                $command =
//                    str_replace('bin/magento', 'dev/tests/integration/bin/magento', $command);
//                $command = $params . ' ' . $command;
//
//                return exec("{$command} > /dev/null &");
//            });
//    }

    /**
     * @dataProvider productCreationProvider
     */
    public function testAsyncScheduleBulk($product)
    {
        $response = $this->saveProductAsync($product);
        $this->assertArrayHasKey(self::BULK_UUID_KEY, $response);
        $this->assertNotNull($response[self::BULK_UUID_KEY]);
    }

    /**
     * @param string $sku
     * @param string|null $storeCode
     * @return array|bool|float|int|string
     */
    private function getProduct($sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::REST_RESOURCE_PATH . '/' . $sku,
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, [ProductInterface::SKU => $sku], null, $storeCode);

        return $response;
    }

    /**
     * @return array
     */
    public function productCreationProvider()
    {
        $productBuilder = function ($data) {
            return array_replace_recursive(
                $this->getSimpleProductData(),
                $data
            );
        };

        return [
            [$productBuilder([ProductInterface::TYPE_ID => 'simple', ProductInterface::SKU => 'psku-test-1'])],
            [$productBuilder([ProductInterface::TYPE_ID => 'virtual', ProductInterface::SKU => 'psku-test-2'])],
        ];
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    private function getSimpleProductData($productData = [])
    {
        return [
            ProductInterface::SKU              => isset($productData[ProductInterface::SKU])
                ? $productData[ProductInterface::SKU] : uniqid('sku-', true),
            ProductInterface::NAME             => isset($productData[ProductInterface::NAME])
                ? $productData[ProductInterface::NAME] : uniqid('sku-', true),
            ProductInterface::VISIBILITY       => 4,
            ProductInterface::TYPE_ID          => 'simple',
            ProductInterface::PRICE            => 3.62,
            ProductInterface::STATUS           => 1,
            ProductInterface::TYPE_ID          => 'simple',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            'custom_attributes'                => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ],
        ];
    }

    /**
     * @param $product
     * @param string|null $storeCode
     * @return mixed
     */
    private function saveProductAsync($product, $storeCode = null)
    {
        if (isset($product['custom_attributes'])) {
            for ($i = 0; $i < sizeof($product['custom_attributes']); $i++) {
                if ($product['custom_attributes'][$i]['attribute_code'] == 'category_ids'
                    && !is_array($product['custom_attributes'][$i]['value'])
                ) {
                    $product['custom_attributes'][$i]['value'] = [""];
                }
            }
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_RESOURCE_PATH,
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['product' => $product];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    /**
     * Delete Product by rest request without async
     *
     * @param string $sku
     * @return boolean
     */
    private function deleteProductRest($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::REST_RESOURCE_PATH . '/' . $sku,
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['sku' => $sku]);
    }

    /**
     * @dataProvider productCreationProvider
     */
    public function testAsyncProductCreation($product)
    {
        $restProduct = $this->getProduct($product[ProductInterface::SKU]);
        $this->assertArrayHasKey('id', $restProduct);
        $this->assertGreaterThan(0, $restProduct['id']);
        $this->deleteProductRest($product[ProductInterface::SKU]);//removing product immediately after test passed
    }

//    /**
//     * Checks that pid files are created
//     *
//     * @return void
//     */
//    public function testCheckThatAsyncPidFilesWasCreated()
//    {
//        $config = $this->config;
//        $config['cron_consumers_runner'] = ['cron_run' => true];
//        $this->writeConfig($config);
//
//        $this->consumersRunner->run();
//
//        foreach ($this->consumerConfig->getConsumers() as $consumer) {
//            if ($consumer->getName() == self::ASYNC_CONSUMER_NAME) {
//                $this->waitConsumerPidFile($consumer->getName());
//            }
//        }
//    }
//
//    /**
//     * Tests running of specific consumer and his re-running when it is working
//     *
//     * @return void
//     */
//    public function testAsyncConsumerAndRerun()
//    {
//        $specificConsumer = self::ASYNC_CONSUMER_NAME;
//        $config = $this->config;
//        $config['cron_consumers_runner'] =
//            ['consumers' => [$specificConsumer], 'max_messages' => null, 'cron_run' => true];
//        $this->writeConfig($config);
//
//        $this->reRunConsumersAndCheckPidFiles($specificConsumer);
//        $this->assertGreaterThan(0, $this->pid->getPid($specificConsumer));
//    }


//    /**
//     * @param string $specificConsumer
//     * @return void
//     */
//    private function reRunConsumersAndCheckPidFiles($specificConsumer)
//    {
//        $this->consumersRunner->run();
//
//        sleep(20);
//
//        foreach ($this->consumerConfig->getConsumers() as $consumer) {
//            $consumerName = $consumer->getName();
//            $pidFilePath = $this->pid->getPidFilePath($consumerName);
//
//            if ($consumerName === $specificConsumer) {
//                $this->assertTrue(file_exists($pidFilePath));
//            } else {
//                $this->assertFalse(file_exists($pidFilePath));
//            }
//        }
//    }
//
//    /**
//     * Tests disabling cron job which runs consumers
//     *
//     * @return void
//     */
//    public function testCronJobDisabled()
//    {
//        $config = $this->config;
//        $config['cron_consumers_runner'] = ['cron_run' => false];
//
//        $this->writeConfig($config);
//
//        $this->consumersRunner->run();
//
//        sleep(20);
//
//        foreach ($this->consumerConfig->getConsumers() as $consumer) {
//            $pidFilePath = $this->pid->getPidFilePath($consumer->getName());
//            $this->assertFalse(file_exists($pidFilePath));
//        }
//    }
//
//    /**
//     * @param string $consumerName
//     * @return void
//     */
//    private function waitConsumerPidFile($consumerName)
//    {
//        $pidFilePath = $this->pid->getPidFilePath($consumerName);
//        $i = 0;
//        do {
//            sleep(1);
//        } while (!file_exists($pidFilePath) && ($i++ < 60));
//
//        sleep(30);
//
//        if (!file_exists($pidFilePath)) {
//            $this->fail($consumerName . ' pid file does not exist.');
//        }
//    }
//
//    /**
//     * @return array
//     */
//    private function loadConfig()
//    {
//        return $this->reader->load(ConfigFilePool::APP_ENV);
//    }
//
//    /**
//     * @param array $config
//     * @return void
//     */
//    private function writeConfig(array $config)
//    {
//        /** @var Writer $writer */
//        $writer = $this->objectManager->get(Writer::class);
//        $writer->saveConfig([ConfigFilePool::APP_ENV => $config]);
//    }
//
//    /**
//     * @inheritdoc
//     */
//    protected function tearDown()
//    {
//        foreach ($this->consumerConfig->getConsumers() as $consumer) {
//            $consumerName = $consumer->getName();
//            $pid = $this->pid->getPid($consumerName);
//
//            if ($pid && $this->pid->isRun($consumerName)) {
//                posix_kill($pid, SIGKILL);
//            }
//
//            $path = $this->pid->getPidFilePath($consumerName);
//            if (file_exists($path)) {
//                unlink($path);
//            }
//        }
//
//        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
//            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
//            "<?php\n return array();\n"
//        );
//        $this->writeConfig($this->config);
//        $this->appConfig->reinit();
//    }
}
