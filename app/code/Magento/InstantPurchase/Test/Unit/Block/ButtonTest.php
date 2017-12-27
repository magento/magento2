<?php

namespace Magento\InstantPurchase\Test\Unit\Block;

use Magento\InstantPurchase\Block\Button;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InstantPurchase\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\UrlInterface;

class ButtonTest extends TestCase
{
    const STORE_ID = 1;

    /**
     * @var Button
     */
    private $button;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMock();

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::STORE_ID);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->button = $objectManager->getObject(
            Button::class,
            [
                'instantPurchaseConfig' => $this->configMock,
                '_storeManager' => $this->storeManagerMock,
                '_urlBuilder' => $this->urlMock,
            ]
        );
    }


    /**
     * @param bool $status
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($status)
    {
        $this->configMock->expects($this->once())
            ->method('isModuleEnabled')
            ->with(self::STORE_ID)
            ->willReturn($status);

        $this->assertEquals($status, $this->button->isEnabled());
    }

    public function testGetJsLayout()
    {
        $buttonText = 'Instant Purchase';
        $url = 'https://magento2.dev/instantpurchase/button/placeOrder';

        $this->configMock->expects($this->once())
            ->method('getButtonText')
            ->with(self::STORE_ID)
            ->willReturn($buttonText);

        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('instantpurchase/button/placeOrder', ['_secure' => true])
            ->willReturn($url);

        $this->assertEquals(
            $this->getExpectedJsLayout($buttonText, $url),
            $this->button->getJsLayout()
        );
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            'enabled' => [true],
            'disabled' => [false],
        ];
    }

    /**
     * @param string $buttonText
     * @param string $url
     * @return string
     */
    private function getExpectedJsLayout($buttonText, $url)
    {
        return \Zend_Json::encode([
            'components' => [
                'instant-purchase' => [
                    'config' => [
                        'buttonText' => $buttonText,
                        'purchaseUrl' => $url,
                    ],
                ],
            ],
        ]);
    }
}
