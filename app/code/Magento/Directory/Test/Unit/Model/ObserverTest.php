<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Directory\Model\Currency\Import\Factory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Observer;
use Magento\Directory\Model\Currency\Import\ImportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Directory\Model\Observer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends TestCase
{
    /**
     * @var string
     */
    private const STUB_SENDER = 'Sender';

    /**
     * @var string
     */
    private const STUB_ERROR_TEMPLATE = 'currency_import_error_email_template';

    /**
     * @var Factory|MockObject
     */
    private $importFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $currencyFactoryMock;

    /**
     * @var StateInterface|MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var Observer
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->importFactoryMock = $this->createMock(Factory::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->transportBuilderMock = $this->createMock(TransportBuilder::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->currencyFactoryMock = $this->createMock(CurrencyFactory::class);
        $this->inlineTranslationMock = $this->getMockForAbstractClass(StateInterface::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Observer::class,
            [
                'importFactory' => $this->importFactoryMock,
                'scopeConfig' => $this->scopeConfigMock,
                'transportBuilder' => $this->transportBuilderMock,
                'storeManager' => $this->storeManagerMock,
                'currencyFactory' => $this->currencyFactoryMock,
                'inlineTranslation' => $this->inlineTranslationMock
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testScheduledUpdateCurrencyRates(): void
    {
        $importWarnings = ['WARNING: error1', 'WARNING: error2'];
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == Observer::IMPORT_ENABLE && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return 1;
                } elseif ($arg1 == Observer::CRON_STRING_PATH && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return '* * * * *';
                } elseif ($arg1 == Observer::IMPORT_SERVICE && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return 'fixerio';
                } elseif ($arg1 == Observer::XML_PATH_ERROR_RECIPIENT && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return 'test1@email.com,test2@email.com';
                } elseif ($arg1 == Observer::XML_PATH_ERROR_TEMPLATE && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return self::STUB_ERROR_TEMPLATE;
                } elseif ($arg1 == Observer::XML_PATH_ERROR_IDENTITY && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return self::STUB_SENDER;
                }
            });
        $import = $this->getMockForAbstractClass(ImportInterface::class);
        $import->expects($this->once())->method('fetchRates')
            ->willReturn([]);
        $import->expects($this->once())->method('getMessages')
            ->willReturn(['error1', 'error2']);
        $this->importFactoryMock->expects($this->once())->method('create')
            ->with('fixerio')
            ->willReturn($import);
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with(self::STUB_ERROR_TEMPLATE)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => FrontNameResolver::AREA_CODE, 'store' => Store::DEFAULT_STORE_ID])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(['warnings' => join("\n", $importWarnings)])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with(self::STUB_SENDER)
            ->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->with(['test1@email.com', 'test2@email.com'])
            ->willReturnSelf();
        $transport = $this->getMockForAbstractClass(TransportInterface::class);

        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage')
            ->willReturnSelf();
        $this->inlineTranslationMock->expects($this->once())->method('suspend')->willReturnSelf();
        $this->inlineTranslationMock->expects($this->once())->method('resume')->willReturnSelf();

        $this->model->scheduledUpdateCurrencyRates([]);
    }
}
