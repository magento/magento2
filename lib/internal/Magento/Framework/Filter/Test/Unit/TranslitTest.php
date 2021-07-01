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

    /**
     * @var string[]
     */
    protected $automatedConversions = [
        'À' => 'a',
        'Á' => 'a',
        'Â' => 'a',
        'Ä' => 'a',
        'Å' => 'a',
        'Ç' => 'c',
        'È' => 'e',
        'É' => 'e',
        'Ë' => 'e',
        'Ì' => 'i',
        'Í' => 'i',
        'Î' => 'i',
        'Ï' => 'i',
        'Ò' => 'o',
        'Ó' => 'o',
        'Ô' => 'o',
        'Õ' => 'o',
        'Ö' => 'o',
        'Ù' => 'u',
        'Ú' => 'u',
        'Û' => 'u',
        'Ü' => 'u',
        'Ý' => 'y',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ý' => 'y',
        'ÿ' => 'y',
        'Ā' => 'a',
        'ā' => 'a',
        'Ă' => 'a',
        'ă' => 'a',
        'Ą' => 'a',
        'ą' => 'a',
        'Ć' => 'c',
        'ć' => 'c',
        'Ĉ' => 'c',
        'ĉ' => 'c',
        'Ċ' => 'c',
        'ċ' => 'c',
        'Č' => 'c',
        'č' => 'c',
        'Ď' => 'd',
        'ď' => 'd',
        'Ē' => 'e',
        'ē' => 'e',
        'Ĕ' => 'e',
        'ĕ' => 'e',
        'Ė' => 'e',
        'ė' => 'e',
        'Ę' => 'e',
        'ę' => 'e',
        'Ě' => 'e',
        'ě' => 'e',
        'Ĝ' => 'g',
        'ĝ' => 'g',
        'Ğ' => 'g',
        'ğ' => 'g',
        'Ġ' => 'g',
        'ġ' => 'g',
        'Ģ' => 'g',
        'ģ' => 'g',
        'Ĥ' => 'h',
        'ĥ' => 'h',
        'Ĩ' => 'i',
        'ĩ' => 'i',
        'Ī' => 'i',
        'ī' => 'i',
        'Ĭ' => 'i',
        'ĭ' => 'i',
        'Į' => 'i',
        'į' => 'i',
        'İ' => 'i',
        'Ĵ' => 'j',
        'ĵ' => 'j',
        'Ķ' => 'k',
        'ķ' => 'k',
        'Ĺ' => 'l',
        'ĺ' => 'l',
        'Ļ' => 'l',
        'ļ' => 'l',
        'Ľ' => 'l',
        'ľ' => 'l',
        'Ń' => 'n',
        'ń' => 'n',
        'Ņ' => 'n',
        'ņ' => 'n',
        'Ň' => 'n',
        'ň' => 'n',
        'Ō' => 'o',
        'ō' => 'o',
        'Ŏ' => 'o',
        'ŏ' => 'o',
        'Ő' => 'o',
        'ő' => 'o',
        'Ŕ' => 'r',
        'ŕ' => 'r',
        'Ŗ' => 'r',
        'ŗ' => 'r',
        'Ř' => 'r',
        'ř' => 'r',
        'Ś' => 's',
        'ś' => 's',
        'Ŝ' => 's',
        'ŝ' => 's',
        'Ş' => 's',
        'ş' => 's',
        'Š' => 's',
        'š' => 's',
        'Ţ' => 't',
        'ţ' => 't',
        'Ť' => 't',
        'ť' => 't',
        'Ũ' => 'u',
        'ũ' => 'u',
        'Ū' => 'u',
        'ū' => 'u',
        'Ŭ' => 'u',
        'ŭ' => 'u',
        'Ů' => 'u',
        'ů' => 'u',
        'Ű' => 'u',
        'ű' => 'u',
        'Ų' => 'u',
        'ų' => 'u',
        'Ŵ' => 'w',
        'ŵ' => 'w',
        'Ŷ' => 'y',
        'ŷ' => 'y',
        'Ÿ' => 'y',
        'Ź' => 'z',
        'ź' => 'z',
        'Ż' => 'z',
        'ż' => 'z',
        'Ž' => 'z',
        'ž' => 'z',
        'Ơ' => 'o',
        'ơ' => 'o',
        'Ư' => 'u',
        'ư' => 'u',
        'Ǎ' => 'a',
        'ǎ' => 'a',
        'Ǐ' => 'i',
        'ǐ' => 'i',
        'Ǒ' => 'o',
        'ǒ' => 'o',
        'Ǔ' => 'u',
        'ǔ' => 'u',
        'Ǖ' => 'u',
        'ǖ' => 'u',
        'Ǘ' => 'u',
        'ǘ' => 'u',
        'Ǚ' => 'u',
        'ǚ' => 'u',
        'Ǜ' => 'u',
        'ǜ' => 'u',
        'Ǻ' => 'a',
        'ǻ' => 'a',
        'Ǽ' => 'ae',
        'ǽ' => 'ae',
        'Ǿ' => 'o',
        'ǿ' => 'o',
        'Ї' => 'i',
        'ї' => 'i',
        'ά' => 'a',
        'Ά' => 'a',
        'έ' => 'e',
        'Έ' => 'e',
        'ή' => 'i',
        'ί' => 'i',
        'ϊ' => 'i',
        'ΐ' => 'i',
        'Ί' => 'i',
        'ό' => 'o',
        'Ό' => 'o',
        'ύ' => 'u',
        'Ύ' => 'y',
        'ώ' => 'o',
        'Ώ' => 'o',
    ];

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

        $data = [
            ['test', 'test', 'test', $isIconv],
            ['привет мир', 'privet mir', 'privet mir', $isIconv],
            [
                'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz',
                'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz',
                'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz',
                $isIconv
            ],
            [
                'Brasília, português, Hà Nội, tiếng Việt, Москва, русский язык',
                'Brasilia, portugues, Ha Noi, tieng Viet, moskva, russkij jazyk',
                'Brasilia, portugues, Ha Noi, tieng Viet, moskva, russkij jazyk',
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

        foreach ($this->automatedConversions as $from => $to) {
            $data[] = [ $from, $to, $to, $isIconv ];
        }

        return $data;
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
