<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Helper\Data
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\Store
     */
    protected $storeMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $pricingCurrencyMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new \Magento\Sales\Helper\Data(
            $contextMock,
            $storeManagerMock,
            $appStateMock,
            $pricingCurrencyMock
        );

        $this->storeMock = $this->getMockBuilder('Magento\Sales\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendNewOrderConfirmationEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity::XML_PATH_EMAIL_ENABLED,
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
            \Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity::XML_PATH_EMAIL_ENABLED,
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
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeMock
            )
            ->will($this->returnValue($returnValue));
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
