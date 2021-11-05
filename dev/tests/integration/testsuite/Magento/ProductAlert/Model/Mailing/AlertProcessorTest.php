<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Translate as PhraseRendererTranslate;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\Translate;
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
}
