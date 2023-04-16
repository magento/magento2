<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Mail\EmailMessage;
use Magento\ProductAlert\Test\Fixture\PriceAlert as PriceAlertFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\ObjectManager;
use Magento\Translation\Test\Fixture\Translation as TranslationFixture;
use PHPUnit\Framework\TestCase;

/**
 * Test for Product Alert observer
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlertProcessorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var AlertProcessor
     */
    private $alertProcessor;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->publisher = $this->objectManager->get(Publisher::class);
        $this->alertProcessor = $this->objectManager->get(AlertProcessor::class);

        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        Config('catalog/productalert/allow_price', 1),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            PriceAlertFixture::class,
            [
                'customer_id' => '$customer.id$',
                'product_id' => '$product.id$',
            ]
        ),
    ]
    public function testProcess()
    {
        $customerId = (int) $this->fixtures->get('customer')->getId();
        $customerName = $this->fixtures->get('customer')->getName();
        $this->processAlerts($customerId);

        $messageContent = $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent();
        /** Checking is the email was sent */
        $this->assertStringContainsString(
            $customerName,
            $messageContent
        );
        $this->assertStringContainsString(
            'Price change alert! We wanted you to know that prices have changed for these products:',
            $messageContent
        );
    }

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$', 'code' => 'pt_br_store'], 'store2'),
        DataFixture(CustomerFixture::class, ['website_id' => 1], as: 'customer1'),
        DataFixture(CustomerFixture::class, ['website_id' => '$website2.id$'], as: 'customer2'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            PriceAlertFixture::class,
            [
                'customer_id' => '$customer1.id$',
                'product_id' => '$product.id$',
                'store_id' => 1,
            ]
        ),
        DataFixture(
            PriceAlertFixture::class,
            [
                'customer_id' => '$customer2.id$',
                'product_id' => '$product.id$',
                'store_id' => '$store2.id$',
            ]
        ),
        DataFixture(
            TranslationFixture::class,
            [
                'string' => 'Price change alert! We wanted you to know that prices have changed for these products:',
                'translate' => 'Alerte changement de prix! Nous voulions que vous sachiez' .
                    ' que les prix ont changé pour ces produits:',
                'locale' => 'fr_FR',
            ],
            'frTxt'
        ),
        DataFixture(
            TranslationFixture::class,
            [
                'string' => 'Price change alert! We wanted you to know that prices have changed for these products:',
                'translate' => 'Alerta de mudanca de preco! Queriamos que voce soubesse' .
                    ' que os precos mudaram para esses produtos:',
                'locale' => 'pt_BR',
            ],
            'ptTxt'
        ),
        Config('catalog/productalert/allow_price', 1),
        Config('general/locale/code', 'fr_FR', ScopeInterface::SCOPE_STORE, 'default'),
        Config('general/locale/code', 'pt_BR', ScopeInterface::SCOPE_STORE, 'pt_br_store'),
    ]
    public function testEmailShouldBeTranslatedToStoreLanguage()
    {
        $customer1Id = (int) $this->fixtures->get('customer1')->getId();
        $customer2Id = (int) $this->fixtures->get('customer2')->getId();
        $website2Id = (int) $this->fixtures->get('website2')->getId();
        $frTxt = $this->fixtures->get('frTxt')->getTranslate();
        $ptTxt = $this->fixtures->get('ptTxt')->getTranslate();

        // Check email from main website
        $this->processAlerts($customer1Id);
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString('/frontend/Magento/luma/fr_FR/', $messageContent);
        $this->assertStringContainsString($frTxt, $messageContent);

        // Check email from second website
        $this->processAlerts($customer2Id, $website2Id);
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString('/frontend/Magento/luma/pt_BR/', $messageContent);
        $this->assertStringContainsString($ptTxt, $messageContent);
    }

    #[
        Config('catalog/productalert/allow_price', 1),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            PriceAlertFixture::class,
            [
                'customer_id' => '$customer.id$',
                'product_id' => '$product.id$',
            ]
        ),
    ]
    public function testCustomerShouldGetEmailForEveryProductPriceDrop(): void
    {
        $customerId = (int) $this->fixtures->get('customer')->getId();
        $productId = (int) $this->fixtures->get('product')->getId();
        $this->processAlerts($customerId);

        $this->assertStringContainsString(
            '$10.00',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );

        // Intentional: update product without using ProductRepository
        // to prevent changes from being cached on application level
        $product = $this->objectManager->get(ProductFactory::class)->create();
        $productResource = $this->objectManager->get(ProductResourceModel::class);
        $product->setStoreId(Store::DEFAULT_STORE_ID);
        $productResource->load($product, $productId);
        $product->setPrice(5);
        $productResource->save($product);

        $this->processAlerts($customerId);

        $this->assertStringContainsString(
            '$5.00',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
    }

    #[
        Config('catalog/productalert/allow_price', 1),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            PriceAlertFixture::class,
            [
                'customer_id' => '$customer.id$',
                'product_id' => '$product.id$',
            ]
        ),
    ]
    public function testValidateCurrentTheme()
    {
        $customerId = (int) $this->fixtures->get('customer')->getId();
        $this->processAlerts($customerId);

        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $this->getMessageRawContent($message);
        $img = Xpath::getElementsForXpath('//img[@class="photo image"]', $messageContent);
        $this->assertMatchesRegularExpression(
            '/frontend\/Magento\/luma\/.+\/thumbnail.jpg$/',
            $img->item(0)->getAttribute('src')
        );
    }

    /**
     * @param int $customerId
     * @param int $websiteId
     * @param string $alertType
     * @return void
     * @throws \Exception
     */
    private function processAlerts(
        int $customerId,
        int $websiteId = 1,
        string $alertType = AlertProcessor::ALERT_TYPE_PRICE
    ): void {
        $this->alertProcessor->process($alertType, [$customerId], $websiteId);
    }

    /**
     * Returns raw content of provided message
     *
     * @param EmailMessage $message
     * @return string
     */
    private function getMessageRawContent(EmailMessage $message): string
    {
        $emailParts = $message->getBody()->getParts();
        return current($emailParts)->getRawContent();
    }
}
