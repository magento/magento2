<?php

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\InstantPurchase\Model\Config;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->config = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    /**
     * @param bool $enabled
     * @dataProvider isModuleEnabledDataProvider
     */
    public function testIsModuleEnabled($enabled)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::ACTIVE, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($enabled);

        $this->assertEquals($enabled, $this->config->isModuleEnabled(1));
    }

    public function testGetButtonText()
    {
        $buttonText = 'Instant Purchase';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::BUTTON_TEXT, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($buttonText);

        $this->assertEquals($buttonText, $this->config->getButtonText(1));
    }

    public function isModuleEnabledDataProvider()
    {
        return [
            'enabled' => [true],
            'disabled' => [false],
        ];
    }
}
