<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Html;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Html\Date;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\View\Element\Html\Date
 */
class DateTest extends TestCase
{
    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private $secureRendererMock;

    /**
     * @var Date
     */
    private $date;

    protected function setUp(): void
    {
        $escaperStub = $this->createStub(Escaper::class);
        $escaperStub->method('escapeHtml')->willReturnArgument(0);

        $contextStub = $this->createStub(Context::class);
        $contextStub->method('getEscaper')->willReturn($escaperStub);
        $contextStub->method('getEventManager')->willReturn($this->createStub(ManagerInterface::class));
        $contextStub->method('getScopeConfig')->willReturn($this->createStub(ScopeConfigInterface::class));

        $this->secureRendererMock = $this->createMock(SecureHtmlRenderer::class);

        $this->date = (new ObjectManager($this))->getObject(
            Date::class,
            [
                'context' => $contextStub,
                'secureRenderer' => $this->secureRendererMock,
            ]
        );
        $this->date->setData(
            [
                'name' => 'dob',
                'id' => 'dob',
                'value' => '01/02/2000',
                'class' => 'input-text',
                'date_format' => 'M/d/yy',
                'image' => 'calendar.png',
                'years_range' => '-120y:c+nn',
                'max_date' => '-1d',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both',
                'first_day' => 1,
            ]
        );
    }

    /**
     * The calendar initialization script must be rendered by SecureHtmlRenderer and not as a raw tag.
     */
    public function testCalendarScriptIsRenderedBySecureRenderer(): void
    {
        $renderedScript = '<script nonce="test-nonce">calendar</script>';
        $capturedScript = null;
        $this->secureRendererMock->expects($this->once())
            ->method('renderTag')
            ->willReturnCallback(
                function (
                    string $tagName,
                    array $attributes,
                    ?string $content,
                    bool $textContent
                ) use (
                    $renderedScript,
                    &$capturedScript
                ): string {
                    $this->assertSame('script', $tagName);
                    $this->assertSame(['type' => 'text/javascript'], $attributes);
                    $this->assertFalse($textContent);
                    $capturedScript = $content;

                    return $renderedScript;
                }
            );

        $html = $this->date->toHtml();

        $this->assertStringStartsWith(
            '<input type="text" name="dob" id="dob" value="01/02/2000" class="input-text" />',
            $html
        );
        $this->assertStringEndsWith($renderedScript, $html);
        $this->assertSame(1, substr_count($html, '<script'));
        $this->assertStringNotContainsString('<script', (string)$capturedScript);
        $this->assertStringContainsString('require(["jquery", "mage/calendar"]', (string)$capturedScript);
        $this->assertStringContainsString('$("#dob").calendar({', (string)$capturedScript);
        $this->assertStringContainsString('dateFormat: "M/d/yy"', (string)$capturedScript);
        $this->assertStringContainsString('buttonImage: "calendar.png"', (string)$capturedScript);
        $this->assertStringContainsString('yearRange: "-120y:c+nn"', (string)$capturedScript);
        $this->assertStringContainsString('maxDate: "-1d"', (string)$capturedScript);
        $this->assertStringContainsString('changeMonth: true', (string)$capturedScript);
        $this->assertStringContainsString('changeYear: true', (string)$capturedScript);
        $this->assertStringContainsString('showOn: "both"', (string)$capturedScript);
        $this->assertStringContainsString('firstDay: 1', (string)$capturedScript);
        $this->assertStringContainsString('showsTime: false', (string)$capturedScript);
    }

    /**
     * Time format switches the calendar to date and time mode.
     */
    public function testCalendarScriptWithTimeFormat(): void
    {
        $capturedScript = null;
        $this->secureRendererMock->expects($this->once())
            ->method('renderTag')
            ->willReturnCallback(
                function (...$arguments) use (&$capturedScript): string {
                    $capturedScript = $arguments[2];

                    return '';
                }
            );

        $this->date->setTimeFormat('HH:mm');
        $this->date->toHtml();

        $this->assertStringContainsString('showsTime: true', (string)$capturedScript);
        $this->assertStringContainsString('timeFormat: "HH:mm"', (string)$capturedScript);
    }
}
