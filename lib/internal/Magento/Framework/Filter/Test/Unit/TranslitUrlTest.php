<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @param bool $isIconv     */
    #[DataProvider('filterDataProvider')]
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
            ['Anémonas do Japão', 'anemonas-do-japao', 'anemonas-do-japao', $isIconv],
            ['Pão de Açúcar', 'pao-de-acucar', 'pao-de-acucar', $isIconv],
            ['Êxito', 'exito', 'exito', $isIconv],
            ['Път към дома', 'pt-km-doma', 'pt-km-doma', $isIconv],
            ['ПЪТ КЪМ ДОМА', 'pt-km-doma', 'pt-km-doma', $isIconv],
            ['Шофьор', 'shofor', 'shofor', $isIconv],
            ['Объект', 'obekt', 'obekt', $isIconv],
            ['Тверь', 'tver', 'tver', $isIconv],
        ];
    }

    /**
     * Language-specific values for the hard and soft signs come from url/convert,
     * e.g. the official Bulgarian transliteration (ъ => a, ь => y).
     */
    public function testFilterConfiguredCyrillicSigns()
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->expects($this->once())
            ->method('getValue')
            ->with('url/convert', 'default')
            ->willReturn(
                [
                    'hard_sign' => ['from' => 'ъ', 'to' => 'a'],
                    'hard_sign_uc' => ['from' => 'Ъ', 'to' => 'a'],
                    'soft_sign' => ['from' => 'ь', 'to' => 'y'],
                    'soft_sign_uc' => ['from' => 'Ь', 'to' => 'y'],
                ]
            );

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(TranslitUrl::class, ['config' => $config]);

        $this->assertEquals('pat-kam-doma', $this->model->filter('Път към дома'));
        $this->assertEquals('pat-kam-doma', $this->model->filter('ПЪТ КЪМ ДОМА'));
        $this->assertEquals('shofyor', $this->model->filter('Шофьор'));
    }
}
