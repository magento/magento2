<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\CollectionTimeLabel;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Test class for \Magento\Analytics\Block\Adminhtml\System\Config\CollectionTimeLabel
 */
class CollectionTimeLabelTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var CollectionTimeLabel
     */
    private $collectionTimeLabel;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var Form|MockObject
     */
    private $formMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $timeZoneMock;

    /**
     * @var AbstractElement|MockObject
     */
    private $abstractElementMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->abstractElementMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['getComment', 'getElementHtml']
        );

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(Escaper::class);
        $reflection = new \ReflectionClass($this->abstractElementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setValue($this->abstractElementMock, $escaper);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getLocaleDate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->createMock(Form::class);
        $this->timeZoneMock = $this->createMock(TimezoneInterface::class);
        $this->contextMock->method('getLocaleDate')
            ->willReturn($this->timeZoneMock);
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);

        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->collectionTimeLabel = $objectManager->getObject(
            CollectionTimeLabel::class,
            [
                'context' => $this->contextMock,
                'localeResolver' => $this->localeResolverMock
            ]
        );
    }

    /**
     * Test for \Magento\Analytics\Block\Adminhtml\System\Config\CollectionTimeLabel::render()
     */
    public function testRender()
    {
        $timeZone = 'America/New_York';
        $this->abstractElementMock->setForm($this->formMock);
        $this->timeZoneMock->expects($this->once())
            ->method('getConfigTimezone')
            ->willReturn($timeZone);
        $this->abstractElementMock->method('getComment')
            ->willReturn('Eastern Standard Time (America/New_York)');
        $this->localeResolverMock->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');
        $this->assertMatchesRegularExpression(
            "/Eastern Standard Time \(America\/New_York\)/",
            $this->collectionTimeLabel->render($this->abstractElementMock)
        );
    }
}
