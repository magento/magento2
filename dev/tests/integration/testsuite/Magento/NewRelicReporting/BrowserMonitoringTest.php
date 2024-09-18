<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting;

use Magento\NewRelicReporting\Model\NewRelicWrapper;

class BrowserMonitoringTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var string|null
     */
    private ?string $headerJs;

    /**
     * @var string|null
     */
    private ?string $footerJs;

    /**
     * @var NewRelicWrapper|null
     */
    private ?NewRelicWrapper $newRelicWrapperMock = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->headerJs = 'var getBrowserTimingHeader = true;';
        $this->footerJs = 'var getBrowserTimingFooter = true;';
        $this->newRelicWrapperMock = $this->getMockBuilder(NewRelicWrapper::class)
            ->onlyMethods([
                'getBrowserTimingHeader',
                'getBrowserTimingFooter',
                'disableAutorum',
                'isAutoInstrumentEnabled'
            ])
            ->getMock();
        $this->_objectManager->addSharedInstance($this->newRelicWrapperMock, NewRelicWrapper::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(NewRelicWrapper::class);
        $this->headerJs = null;
        $this->footerJs = null;
        $this->newRelicWrapperMock = null;
        parent::tearDown();
    }

    /**
     * @param string $url
     * @return void
     * @dataProvider dataProvider
     */
    public function testPageShouldContainBrowserMonitoringScripts(string $url): void
    {
        $this->newRelicWrapperMock->expects($this->once())
            ->method('getBrowserTimingHeader')
            ->with(false)
            ->willReturn($this->headerJs);
        $this->newRelicWrapperMock->expects($this->once())
            ->method('getBrowserTimingFooter')
            ->with(false)
            ->willReturn($this->footerJs);
        $this->newRelicWrapperMock->method('isAutoInstrumentEnabled')
            ->willReturn(true);

        // check that disableAutorum is called
        $this->newRelicWrapperMock->expects($this->once())
            ->method('disableAutorum');

        $this->dispatch($url);
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $body = $this->getResponse()->getBody();
        // check that header js is the first script before css
        $this->assertMatchesRegularExpression(
            '/<script[^>]*>\s*' . $this->headerJs . '\s*<\/script>.*<link[^>]*\.css"[^>]*>/s',
            $body
        );
        // check that header js is the first script before js
        $this->assertMatchesRegularExpression(
            '/<script[^>]*>\s*' . $this->headerJs . '\s*<\/script>.*<script[^>]*requirejs\/require.js"[^>]*>/s',
            $body
        );
        // check that footer js is the last script before body closing tag;
        $this->assertMatchesRegularExpression(
            '/<script[^>]*>\s*' . $this->footerJs . '\s*<\/script>\s*<\/body>/s',
            $body
        );
    }

    /**
     * @param string $url
     * @return void
     * @dataProvider dataProvider
     */
    public function testPageShouldNotContainBrowserMonitoringScripts(string $url): void
    {
        $this->newRelicWrapperMock->expects($this->never())
            ->method('getBrowserTimingHeader');
        $this->newRelicWrapperMock->expects($this->never())
            ->method('getBrowserTimingFooter');
        $this->newRelicWrapperMock->method('isAutoInstrumentEnabled')
            ->willReturn(false);

        // check that disableAutorum is not called
        $this->newRelicWrapperMock->expects($this->never())
            ->method('disableAutorum');

        $this->dispatch($url);
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $body = $this->getResponse()->getBody();
        // check that header js is not on the page
        $this->assertStringNotContainsString($this->headerJs, $body);
        // check that header js is not on the page
        $this->assertStringNotContainsString($this->footerJs, $body);
    }

    /**
     * @return array[]
     */
    public static function dataProvider(): array
    {
        return [
            ['backend/admin/dashboard'],
            [''],
            ['checkout/cart/index'],
        ];
    }
}
