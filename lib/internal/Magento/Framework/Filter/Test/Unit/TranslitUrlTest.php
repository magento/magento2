<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class TranslitUrlTest extends TestCase
{
    /**
     * @var TranslitUrl
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(TranslitUrl::class);
    }

    /**
     * @param string $testString
     * @param string $result
     * @param string $resultIconv
     * @param bool $isIconv
     * @dataProvider filterDataProvider
     */
    public function testFilter($testString, $result, $resultIconv, $isIconv)
    {
        if ($isIconv) {
            $this->assertEquals($resultIconv, $this->model->filter($testString));
        } else {
            $this->assertEquals($result, $this->model->filter($testString));
        }
    }

    /**
     * @return array
     */
    public static function filterDataProvider()
    {
        $isIconv = '"libiconv"' == ICONV_IMPL;
        return [
            ['test', 'test', 'test', $isIconv],
            ['привет мир', 'privet-mir', 'privet-mir', $isIconv],
            [
                'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz',
                'weiss-goldmann-gobel-weiss-gothe-goethe-und-gotz',
                'weiss-goldmann-gobel-weiss-gothe-goethe-und-gotz',
                $isIconv
            ],
            [
                '❤ ☀ ☆ ☂ ☻ ♞ ☯ ☭ ☢ € → ☎ ❄ ♫ ✂ ▷ ✇ ♎ ⇧ ☮',
                '',
                'eur',
                $isIconv
            ],
            ['™', 'tm', 'tm', $isIconv],
            ['ñandú', 'nandu', 'nandu', $isIconv],
            ['ÑANDÚ', 'nandu', 'nandu', $isIconv],
        ];
    }
}
