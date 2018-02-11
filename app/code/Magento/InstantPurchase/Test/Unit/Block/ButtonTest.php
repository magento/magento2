<?php

namespace Magento\InstantPurchase\Test\Unit\Block;

use Magento\InstantPurchase\Block\Button;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\InstantPurchase\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

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

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->contextMock->method('getStoreManager')
            ->willReturn($this->storeManagerMock);
        $this->contextMock->method('getUrlBuilder')
            ->willReturn($this->urlMock);

        $this->storeMock->method('getId')
            ->willReturn(self::STORE_ID);

        $this->storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);

        $this->button = new Button(
            $this->contextMock,
            $this->configMock
        );
    }

    public function testGetJsLayout()
    {
        $buttonText = 'Instant Purchase';
        $url = 'https://magento2.dev/instantpurchase/button/placeOrder';

        $this->configMock->method('getButtonText')
            ->willReturn($buttonText);

        $this->urlMock->method('getUrl')
            ->willReturn($url);

        $this->assertEquals(
            $this->getExpectedJsLayout($buttonText, $url),
            $this->button->getJsLayout()
        );
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
