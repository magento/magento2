<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests disabled cart rules for customer's cart
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TotalsInformationManagement extends WebapiAbstract
{
    private const SERVICE_OPERATION = 'calculate';
    private const SERVICE_NAME = 'checkoutTotalsInformationManagementV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/carts/mine/totals-information';
    private const QUOTE_RESERVED_ORDER_ID = 'test01';
    private const SALES_RULE_ID = 'Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition/salesRuleId';
    private const CUSTOMER_EMAIL = 'customer@example.com';
    private const CUSTOMER_PASSWORD = 'password';

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @var string[]
     */
    private $consumers = ['sales.rule.quote.trigger.recollect'];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $objectManager->create(PublisherConsumerController::class, [
            'consumers' => $this->consumers,
            'logFilePath' => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
            'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
        ]);

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail($e->getMessage());
        }

        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * Test sales rule changes should be persisted in the database
     *
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition.php
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     */
    public function testCalculate()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        /** @var \Magento\SalesRule\Model\Rule $salesRule */
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $quote = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\QuoteFactory::class)->create();
        $quote->load(self::QUOTE_RESERVED_ORDER_ID, 'reserved_order_id');
        $quoteIdMask = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
        $quoteIdMask->load($quote->getId(), 'quote_id');
        $salesRuleId = $registry->registry(self::SALES_RULE_ID);
        $salesRule = Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\RuleFactory::class)->create();
        $salesRule->load($salesRuleId);
        $this->assertContains($salesRule->getRuleId(), str_getcsv($quote->getAppliedRuleIds(), ',', '"', '\\'));
        $this->assertEquals(0, $quote->getTriggerRecollect());
        $salesRule->setIsActive(0);
        $salesRule->save();
        $this->assertQuoteTriggerRecollectIsUpdated($quote);
        $response = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH,
                    'httpMethod' => Request::HTTP_METHOD_POST,
                    'token' => Bootstrap::getObjectManager()
                        ->create(
                            \Magento\Integration\Api\CustomerTokenServiceInterface::class
                        )
                        ->createCustomerAccessToken(
                            self::CUSTOMER_EMAIL,
                            self::CUSTOMER_PASSWORD
                        )
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => self::SERVICE_VERSION,
                    'operation' => self::SERVICE_NAME . self::SERVICE_OPERATION,
                ],
            ],
            [
                'cartId' => $quote->getId(),
                'addressInformation' => [
                    'address' => []
                ]
            ]
        );
        $this->assertNotEmpty($response);
        $quote->load(self::QUOTE_RESERVED_ORDER_ID, 'reserved_order_id');
        $this->assertNotContains($salesRule->getId(), str_getcsv($quote->getAppliedRuleIds(), ',', '"', '\\'));
    }

    /**
     * Assert that quote trigger_recollect value was set to 1
     *
     * @param Quote $quote
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function assertQuoteTriggerRecollectIsUpdated(Quote $quote) : void
    {
        $quoteResource = Bootstrap::getObjectManager()->get(QuoteResourceModel::class);
        $resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $select = $resourceConnection->getConnection()
            ->select()
            ->from($quoteResource->getMainTable(), ['trigger_recollect'])
            ->where('entity_id = ?', (int) $quote->getId());
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                function (ResourceConnection $resourceConnection, Select $select) {
                    return (int) $resourceConnection->getConnection()->fetchOne($select) === 1;
                },
                [$resourceConnection, $select]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("trigger_recollect was not updated for quote ID {$quote->getId()}");
        }
    }
}
