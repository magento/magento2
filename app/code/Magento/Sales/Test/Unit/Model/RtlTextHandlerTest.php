<?php

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Model\RtlTextHandler;
use PHPUnit\Framework\TestCase;

class RtlTextHandlerTest extends TestCase
{
    /**
     * @var RtlTextHandler
     */
    private $rtlTextHandler;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    protected function setUp(): void
    {
        $this->stringUtils = new StringUtils();
        $this->rtlTextHandler = new RtlTextHandler($this->stringUtils);
    }

    /**
     * @param string $str
     * @param bool $expected
     * @dataProvider provideRtlTexts
     */
    public function testIsRtlText(string $str, bool $expected): void
    {
        $this->assertEquals($expected, $this->rtlTextHandler->isRtlText($str));
    }

    /**
     * @param string $str
     * @param bool $expected
     * @dataProvider provideRtlTexts
     */
    public function testReverseArabicText(string $str, bool $expected): void
    {
        $expectedStr = $expected ? $this->stringUtils->strrev($str) : $str;

        $this->assertEquals($expectedStr, $this->rtlTextHandler->reverseArabicText($str));
    }

    public function provideRtlTexts(): array
    {
        return [
            ['Adeline Jacobson', false],//English
            ['Odell Fisher', false],//English
            ['Панов Аркадий Львович', false],//Russian
            ['Вероника Сергеевна Игнатьева', false],//Russian
            ['Mehmet Arnold-Döring', false],//German
            ['Herr Prof. Dr. Gerald Schüler B.A.', false],//German
            ['نديم مقداد نعمان القحطاني', true],//Arabic
            ['شهاب الفرحان', true],//Arabic
            ['צבר קרליבך', true],//Hebrew
            ['גורי מייזליש', true],//Hebrew
            ['اتابک بهشتی', true],//Persian
            ['مهداد محمدی', true],//Persian
        ];
    }
}
