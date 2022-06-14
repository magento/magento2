<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sitemap\Model\EmailNotification;
use Magento\Sitemap\Model\Observer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Sitemap\Model\EmailNotification
 */
class EmailNotificationTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var EmailNotification
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var StateInterface|MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->transportBuilderMock = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(StateInterface::class)
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            EmailNotification::class,
            [
                'inlineTranslation' => $this->inlineTranslationMock,
                'scopeConfig' => $this->scopeConfigMock,
                'transportBuilder' => $this->transportBuilderMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testSendErrors(): void
    {
        $exception = 'Sitemap Exception';
        $transport = $this->getMockForAbstractClass(TransportInterface::class);

        $this->scopeConfigMock
            ->method('getValue')
            ->withConsecutive([Observer::XML_PATH_ERROR_TEMPLATE, ScopeInterface::SCOPE_STORE])
            ->willReturn(['error-recipient@example.com']);

        $this->inlineTranslationMock->expects($this->once())
            ->method('suspend');

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with([
                'area' => FrontNameResolver::AREA_CODE,
                'store' => Store::DEFAULT_STORE_ID,
            ])->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(['warnings' => $exception])->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');

        $this->inlineTranslationMock->expects($this->once())
            ->method('resume');

        $this->model->sendErrors(['Sitemap Exception']);
    }
}
