<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Helper\Data;
use Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject|\Magento\Sales\Model\Store
     */
    protected $storeMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pricingCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->helper = new Data(
            $contextMock,
            $storeManagerMock,
            $appStateMock,
            $pricingCurrencyMock
        );

        $this->storeMock = $this->getMockBuilder(\Magento\Sales\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendNewOrderConfirmationEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            OrderIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewOrderConfirmationEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendNewOrderEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            OrderIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewOrderEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendOrderCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            OrderCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendOrderCommentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendNewShipmentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            ShipmentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewShipmentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendShipmentCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            ShipmentCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendShipmentCommentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendNewInvoiceEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            InvoiceIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewInvoiceEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendInvoiceCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            InvoiceCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendInvoiceCommentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendNewCreditmemoEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            CreditmemoIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewCreditmemoEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendCreditmemoCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            CreditmemoCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendCreditmemoCommentEmail($this->storeMock));
    }

    /**
     * Sets up the scope config mock which will return a specified value for a config flag.
     *
     * @param string $flagName
     * @param bool $returnValue
     * @return void
     */
    protected function setupScopeConfigIsSetFlag($flagName, $returnValue)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $flagName,
                ScopeInterface::SCOPE_STORE,
                $this->storeMock
            )
            ->willReturn($returnValue);
    }

    /**
     * @return array
     */
    public function getScopeConfigValue()
    {
        return [
            [true],
            [false]
        ];
    }
}
