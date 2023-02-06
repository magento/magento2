<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Translate as PhraseRendererTranslate;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\Translate;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for Product Alert observer
 *
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
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
     * @var DesignInterface
     */
    private $design;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->publisher = $this->objectManager->get(Publisher::class);
        $this->alertProcessor = $this->objectManager->get(AlertProcessor::class);

        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $service = $this->objectManager->create(AccountManagementInterface::class);
        $customer = $service->authenticate('customer@example.com', 'password');
        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->setCustomerDataAsLoggedIn($customer);
        $this->design = $this->objectManager->get(DesignInterface::class);
    }

    /**
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testProcess()
    {
        $this->processAlerts();

        /** Checking is the email was sent */
        $this->assertStringContainsString(
            'John Smith,',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Check translations for product alerts
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture fixture_second_store_store general/locale/code pt_BR
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert_with_store.php
     */
    public function testProcessPortuguese()
    {
        // get second store
        $storeRepository = $this->objectManager->create(StoreRepository::class);
        $secondStore = $storeRepository->get('fixture_second_store');

        // check if Portuguese language is specified for the second store
        $storeResolver = $this->objectManager->get(Resolver::class);
        $storeResolver->emulate($secondStore->getId());
        $this->assertEquals('pt_BR', $storeResolver->getLocale());

        // set translation data and check it
        $modulesReader = $this->createPartialMock(Reader::class, ['getModuleDir']);
        $modulesReader->method('getModuleDir')
            ->willReturn(dirname(__DIR__) . '/../_files/i18n');
        /** @var Translate $translator */
        $translator = $this->objectManager->create(Translate::class, ['modulesReader' => $modulesReader]);
        $translation = [
            'Price change alert! We wanted you to know that prices have changed for these products:' =>
                'Alerta de mudanca de preco! Queriamos que voce soubesse que os precos mudaram para esses produtos:'
        ];
        $translator->loadData();
        $this->assertEquals($translation, $translator->getData());
        $this->objectManager->addSharedInstance($translator, Translate::class);
        $this->objectManager->removeSharedInstance(PhraseRendererTranslate::class);
        Phrase::setRenderer($this->objectManager->create(RendererInterface::class));

        // dispatch process() method and check sent message
        $this->processAlerts();
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $expectedText = array_shift($translation);
        $this->assertStringContainsString('/frontend/Magento/luma/pt_BR/', $messageContent);
        $this->assertStringContainsString(substr($expectedText, 0, 50), $messageContent);
    }

    /**
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testCustomerShouldGetEmailForEveryProductPriceDrop(): void
    {
        $this->processAlerts();

        $this->assertStringContainsString(
            '$10.00',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );

        // Intentional: update product without using ProductRepository
        // to prevent changes from being cached on application level
        $product = $this->objectManager->get(ProductFactory::class)->create();
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $productResource = $this->objectManager->get(ProductResourceModel::class);
        $product->setStoreId(Store::DEFAULT_STORE_ID);
        $productResource->load($product, $productRepository->get('simple')->getId());
        $product->setPrice(5);
        $productResource->save($product);

        $this->processAlerts();

        $this->assertStringContainsString(
            '$5.00',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Process price alerts
     */
    private function processAlerts(): void
    {
        $alertType = AlertProcessor::ALERT_TYPE_PRICE;
        $customerId = 1;
        $websiteId = 1;

        $this->publisher->execute($alertType, [$customerId], $websiteId);
        $this->alertProcessor->process($alertType, [$customerId], $websiteId);
    }

    /**
     * Validate the current theme
     *
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testValidateCurrentTheme()
    {
        $this->design->setDesignTheme(
            $this->objectManager->get(ThemeInterface::class)
        );

        $this->processAlerts();

        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $this->getMessageRawContent($message);
        $emailDom = new \DOMDocument();
        $emailDom->loadHTML($messageContent);

        $emailXpath = new \DOMXPath($emailDom);
        $greeting = $emailXpath->query('//img[@class="photo image"]');
        $this->assertStringContainsString(
            'thumbnail.jpg',
            $greeting->item(0)->getAttribute('src')
        );
        $this->assertEquals('Magento/luma', $this->design->getDesignTheme()->getCode());
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
