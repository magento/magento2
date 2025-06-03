<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title as PageTitle;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Theme\Block\Html\Title
 */
class TitleTest extends TestCase
{
    /**
     * Config path to 'Translate Title' header settings
     */
    private const XML_PATH_HEADER_TRANSLATE_TITLE = 'design/header/translate_title';

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;

    /**
     * @var PageTitle|MockObject
     */
    private $pageTitleMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Title
     */
    private $htmlTitle;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->pageConfigMock = $this->createMock(Config::class);
        $this->pageTitleMock = $this->createMock(PageTitle::class);

        $context = $this->objectManagerHelper->getObject(
            Context::class,
            ['pageConfig' => $this->pageConfigMock]
        );

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->htmlTitle = $this->objectManagerHelper->getObject(
            Title::class,
            [
                'context' => $context,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetPageTitleWithSetPageTitle()
    {
        $title = 'some title';

        $this->htmlTitle->setPageTitle($title);
        $this->pageConfigMock->expects($this->never())
            ->method('getTitle');

        $this->assertEquals($title, $this->htmlTitle->getPageTitle());
    }

    /**
     * @param bool $shouldTranslateTitle
     *
     * @return void
     * @dataProvider dataProviderShouldTranslateTitle
     */
    public function testGetPageTitle($shouldTranslateTitle)
    {
        $this->scopeConfigMock->method('isSetFlag')
            ->with(static::XML_PATH_HEADER_TRANSLATE_TITLE, ScopeInterface::SCOPE_STORE)
            ->willReturn($shouldTranslateTitle);
        $title = 'some title';

        $this->pageTitleMock->expects($this->once())
            ->method('getShort')
            ->willReturn($title);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $result = $this->htmlTitle->getPageTitle();

        if ($shouldTranslateTitle) {
            $this->assertInstanceOf(Phrase::class, $result);
        } else {
            $this->assertIsString($result);
        }

        $this->assertEquals($title, $result);
    }

    /**
     * @param bool $shouldTranslateTitle
     *
     * @return void
     * @dataProvider dataProviderShouldTranslateTitle
     */
    public function testGetPageHeadingWithSetPageTitle($shouldTranslateTitle)
    {
        $this->scopeConfigMock->method('isSetFlag')
            ->with(static::XML_PATH_HEADER_TRANSLATE_TITLE, ScopeInterface::SCOPE_STORE)
            ->willReturn($shouldTranslateTitle);

        $title = 'some title';

        $this->htmlTitle->setPageTitle($title);
        $this->pageConfigMock->expects($this->never())
            ->method('getTitle');

        $result = $this->htmlTitle->getPageHeading();

        if ($shouldTranslateTitle) {
            $this->assertInstanceOf(Phrase::class, $result);
        } else {
            $this->assertIsString($result);
        }

        $this->assertEquals($title, $result);
    }

    /**
     * @param bool $shouldTranslateTitle
     *
     * @return void
     * @dataProvider dataProviderShouldTranslateTitle
     */
    public function testGetPageHeading($shouldTranslateTitle)
    {
        $this->scopeConfigMock->method('isSetFlag')
            ->with(static::XML_PATH_HEADER_TRANSLATE_TITLE, ScopeInterface::SCOPE_STORE)
            ->willReturn($shouldTranslateTitle);

        $title = 'some title';

        $this->pageTitleMock->expects($this->once())
            ->method('getShortHeading')
            ->willReturn($title);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $result = $this->htmlTitle->getPageHeading();

        if ($shouldTranslateTitle) {
            $this->assertInstanceOf(Phrase::class, $result);
        } else {
            $this->assertIsString($result);
        }

        $this->assertEquals($title, $result);
    }

    /**
     * @return array
     */
    public static function dataProviderShouldTranslateTitle(): array
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }
}
