<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Directory\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\TestFramework\Helper\ObjectManager */
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

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->importFactory = $this->getMockBuilder('Magento\Directory\Model\Currency\Import\Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\MutableScopeConfig')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $this->transportBuilder = $this->getMockBuilder('Magento\Framework\Mail\Template\TransportBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyFactory = $this->getMockBuilder('Magento\Directory\Model\CurrencyFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->inlineTranslation = $this->getMockBuilder('Magento\Framework\Translate\Inline\StateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            'Magento\Directory\Model\Observer',
            array(
                'importFactory' => $this->importFactory,
                'scopeConfig' => $this->scopeConfig,
                'transportBuilder' => $this->transportBuilder,
                'storeManager' => $this->storeManager,
                'currencyFactory' => $this->currencyFactory,
                'inlineTranslation' => $this->inlineTranslation
            )
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
        $importInterfaceMock = $this->getMockBuilder('Magento\Directory\Model\Currency\Import\Webservicex')
            ->disableOriginalConstructor()
            ->setMethods(['fetchRates', 'getMessages'])
            ->getMock();
        $importInterfaceMock->expects($this->once())
            ->method('fetchRates')
            ->will($this->returnValue(array()));
        $importInterfaceMock->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue(array()));

        $this->importFactory
            ->expects($this->once())
            ->method('create')
            ->with('import-service')
            ->will($this->returnValue($importInterfaceMock));

        $currencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
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
}
