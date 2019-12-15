<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Model;

use Magento\Contact\Model\Config;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
            ->setMethods(['getValue', 'isSetFlag'])
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
     */
    public function testIsEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(config::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->assertTrue(true, $this->model->isEnabled());
    }

    /**
     * Test isNotEnabled()
     *
     * @return void
     */
    public function testIsNotEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(config::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->assertFalse(false, $this->model->isEnabled());
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
            ->with(config::XML_PATH_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE)
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
            ->with(config::XML_PATH_EMAIL_SENDER, ScopeInterface::SCOPE_STORE)
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
            ->with(config::XML_PATH_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE)
            ->willReturn('hello@example.com');

        $this->assertEquals('hello@example.com', $this->model->emailRecipient());
    }
}
