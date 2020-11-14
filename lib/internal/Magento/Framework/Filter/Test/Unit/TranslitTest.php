<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\Translit;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class TranslitTest extends TestCase
{
    /**
     * @var Translit
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Translit::class);
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
    public function filterDataProvider()
    {
        $isIconv = '"libiconv"' == ICONV_IMPL;
        return [
            ['test', 'test', 'test', $isIconv],
            ['привет мир', 'privet mir', 'privet mir', $isIconv],
            [
                'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz',
                'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz',
                'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz',
                $isIconv
            ],
            [
                '❤ ☀ ☆ ☂ ☻ ♞ ☯ ☭ ☢ € → ☎ ❄ ♫ ✂ ▷ ✇ ♎ ⇧ ☮',
                '❤ ☀ ☆ ☂ ☻ ♞ ☯ ☭ ☢ € → ☎ ❄ ♫ ✂ ▷ ✇ ♎ ⇧ ☮',
                '         EUR ->         ',
                $isIconv
            ],
            ['™', 'tm', 'tm', $isIconv],
            ['লক্ষ্য এনালগ ওয়াচ টি ২০', 'laksoa enaalaga oyaoaca tai 20', 'laksoa enaalaga oyaoaca tai 20', $isIconv]
        ];
    }

    public function testFilterConfigured()
    {
        $config = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getValue', 'setValue', 'isSetFlag']
            )->getMock();

        $config->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'url/convert',
            'default'
        )->willReturn(
            ['char8482' => ['from' => '™', 'to' => 'TM']]
        );

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Translit::class, ['config' => $config]);

        $this->assertEquals('TM', $this->model->filter('™'));
    }
}
