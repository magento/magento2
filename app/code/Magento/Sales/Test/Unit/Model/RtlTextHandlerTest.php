<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @param bool $isRtl
     * @dataProvider provideRtlTexts
     */
    public function testIsRtlText(string $str, bool $isRtl): void
    {
        $this->assertEquals($isRtl, $this->rtlTextHandler->isRtlText($str));
    }

    /**
     * @param string $str
     * @param bool $isRtl
     * @dataProvider provideRtlTexts
     */
    public function testReverseRtlText(string $str, bool $isRtl): void
    {
        $expectedStr = $isRtl ? $this->stringUtils->strrev($str) : $str;

        $this->assertEquals($expectedStr, $this->rtlTextHandler->reverseRtlText($str));
    }

    public static function provideRtlTexts(): array
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
            ['مرحبا ماجنت اثنان', true],//Arabic
            ['צבר קרליבך', true],//Hebrew
            ['גורי מייזליש', true],//Hebrew
            ['اتابک بهشتی', true],//Persian
            ['مهداد محمدی', true],//Persian
        ];
    }
}
