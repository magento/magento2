<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Model;

use Magento\Contact\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Contact\Model\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Test isEnabled()
     *
     * @return void
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($isSetFlag, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn($isSetFlag);

        $this->assertEquals($result, $this->model->isEnabled());
    }

    /**
     * Data provider for isEnabled()
     *
     * @return array
     */
    public static function isEnabledDataProvider(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * Test emailTemplate()
     *
     * @return void
     */
    public function testEmailTemplate(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE)
            ->willReturn('contact_email_email_template');

        $this->assertEquals('contact_email_email_template', $this->model->emailTemplate());
    }

    /**
     * Test emailSender()
     *
     * @return void
     */
    public function testEmailSender(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_SENDER, ScopeInterface::SCOPE_STORE)
            ->willReturn('custom2');

        $this->assertEquals('custom2', $this->model->emailSender());
    }

    /**
     * Test emailRecipient()
     *
     * @return void
     */
    public function testEmailRecipient(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE)
            ->willReturn('hello@example.com');

        $this->assertEquals('hello@example.com', $this->model->emailRecipient());
    }
}
