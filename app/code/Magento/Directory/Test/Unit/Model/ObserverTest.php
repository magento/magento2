<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Directory\Model\Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $objectManager;

    /** @var Observer */
    protected $observer;

    /** @var  \Magento\Directory\Model\Currency\Import\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $importFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportBuilder;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Directory\Model\CurrencyFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $currencyFactory;

    /** @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $inlineTranslation;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->importFactory = $this->getMockBuilder(\Magento\Directory\Model\Currency\Import\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\MutableScopeConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $this->transportBuilder = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->inlineTranslation = $this->getMockBuilder(\Magento\Framework\Translate\Inline\StateInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            \Magento\Directory\Model\Observer::class,
            [
                'importFactory' => $this->importFactory,
                'scopeConfig' => $this->scopeConfig,
                'transportBuilder' => $this->transportBuilder,
                'storeManager' => $this->storeManager,
                'currencyFactory' => $this->currencyFactory,
                'inlineTranslation' => $this->inlineTranslation
            ]
        );
    }

    public function testScheduledUpdateCurrencyRates()
    {
        $this->scopeConfig
            ->expects($this->at(0))
            ->method('getValue')
            ->with(Observer::IMPORT_ENABLE, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(1));
        $this->scopeConfig
            ->expects($this->at(1))
            ->method('getValue')
            ->with(Observer::CRON_STRING_PATH, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue('cron-path'));
        $this->scopeConfig
            ->expects($this->at(2))
            ->method('getValue')
            ->with(Observer::IMPORT_SERVICE, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue('import-service'));
        $importInterfaceMock = $this->getMockBuilder(\Magento\Directory\Model\Currency\Import\Webservicex::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchRates', 'getMessages'])
            ->getMock();
        $importInterfaceMock->expects($this->once())
            ->method('fetchRates')
            ->will($this->returnValue([]));
        $importInterfaceMock->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue([]));

        $this->importFactory
            ->expects($this->once())
            ->method('create')
            ->with('import-service')
            ->will($this->returnValue($importInterfaceMock));

        $currencyMock = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveRates', '__wakeup', '__sleep'])
            ->getMock();
        $currencyMock->expects($this->once())
            ->method('saveRates')
            ->will($this->returnValue(null));
        $this->currencyFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($currencyMock));

        $this->observer->scheduledUpdateCurrencyRates(null);
    }

    /**
     * @expectedException \Exception
     */
    public function testScheduledUpdateCurrencyRatesThrowsException()
    {
        $this->scopeConfig->expects($this->exactly(3))
            ->method('getValue')
            ->willReturnMap(
                [
                    [Observer::IMPORT_ENABLE, ScopeInterface::SCOPE_STORE, null, 1],
                    [Observer::CRON_STRING_PATH, ScopeInterface::SCOPE_STORE, null, 'cron-path'],
                    [Observer::IMPORT_SERVICE, ScopeInterface::SCOPE_STORE, null, 'import-service']
                ]
            );

        $this->importFactory
            ->expects($this->once())
            ->method('create')
            ->with('import-service')
            ->willThrowException(new \Exception());

        $this->observer->scheduledUpdateCurrencyRates(null);
    }
}
