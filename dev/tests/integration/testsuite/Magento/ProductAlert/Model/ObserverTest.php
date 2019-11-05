<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

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
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\ObjectManager;

/**
 * Test for Magento\ProductAlert\Model\Observer
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->observer =  $this->_objectManager->get(Observer::class);
        $this->transportBuilder =  $this->_objectManager->get(TransportBuilderMock::class);
        $service = $this->_objectManager->create(AccountManagementInterface::class);
        $customer = $service->authenticate('customer@example.com', 'password');
        $customerSession = $this->_objectManager->get(Session::class);
        $customerSession->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * Test process() method
     *
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testProcess()
    {
        $this->observer->process();
        $this->assertContains(
            'John Smith,',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Check translations for product alerts
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture fixture_second_store_store general/locale/code pt_BR
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert_with_store.php
     */
    public function testProcessPortuguese()
    {
        // get second store
        $storeRepository = $this->_objectManager->create(StoreRepository::class);
        $secondStore = $storeRepository->get('fixture_second_store');

        // check if Portuguese language is specified for the second store
        CacheCleaner::cleanAll();
        $storeResolver = $this->_objectManager->get(Resolver::class);
        $storeResolver->emulate($secondStore->getId());
        $this->assertEquals('pt_BR', $storeResolver->getLocale());

        // set translation data and check it
        $modulesReader = $this->createPartialMock(Reader::class, ['getModuleDir']);
        $modulesReader->expects($this->any())
            ->method('getModuleDir')
            ->willReturn(dirname(__DIR__) . '/_files/i18n');
        /** @var Translate $translator */
        $translator = $this->_objectManager->create(Translate::class, ['modulesReader' => $modulesReader]);
        $translation = [
            'Price change alert! We wanted you to know that prices have changed for these products:' =>
                'Alerta de mudanca de preco! Queriamos que voce soubesse que os precos mudaram para esses produtos:'
        ];
        $translator->loadData();
        $this->assertEquals($translation, $translator->getData());
        $this->_objectManager->addSharedInstance($translator, Translate::class);
        $this->_objectManager->removeSharedInstance(PhraseRendererTranslate::class);
        Phrase::setRenderer($this->_objectManager->create(RendererInterface::class));

        // dispatch process() method and check sent message
        $this->observer->process();
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $expectedText = array_shift($translation);
        $this->assertContains('/frontend/Magento/luma/pt_BR/', $messageContent);
        $this->assertContains(substr($expectedText, 0, 50), $messageContent);
    }
}
