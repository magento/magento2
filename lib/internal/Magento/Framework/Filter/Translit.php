<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Translit filter
 *
 * Process string based on convertation table
 */
class Translit implements \Zend_Filter_Interface
{
    /**
     * A conversion table for symbols that should be replaced before Unicode "marks" are removed from
     * transliterated strings.
     *
     * @var string[]
     */
    protected $convertTable = [
        'Ё' => 'jo',
        'Й' => 'j',
        'й' => 'j',
        'ё' => 'jo',
        'অ' => 'a',
        'আ' => 'aa',
        'ই' => 'i',
        'ঈ' => 'ii',
        'উ' => 'u',
        'ঊ' => 'uu',
        'ঋ' => 'r',
        'ৠ' => 'ri',
        'এ' => 'e',
        'ঐ' => 'ai',
        'ও' => 'o',
        'ঔ' => 'ou',
        'ক' => 'ka',
        'খ' => 'kha',
        'গ' => 'ga',
        'ঘ' => 'gha',
        'ঙ' => 'na',
        'চ' => 'ca',
        'ছ' => 'cha',
        'জ' => 'ja',
        'ঝ' => 'jha',
        'ঞ' => 'na',
        'ট' => 'ta',
        'ঠ' => 'tha',
        'ড' => 'da',
        'ড়' => 'ra',
        'ঢ' => 'dha',
        'ঢ়' => 'rha',
        'ণ' => 'na',
        'ত' => 'ta',
        'ৎ' => 't',
        'থ' => 'tha',
        'দ' => 'da',
        'ধ' => 'dha',
        'ন' => 'na',
        'প' => 'pa',
        'ফ' => 'pha',
        'ব' => 'ba',
        'ভ' => 'bha',
        'ম' => 'ma',
        'য' => 'ya',
        'য়' => 'ya',
        'র' => 'ra',
        'ল' => 'la',
        'শ' => 'sa',
        'ষ' => 'sha',
        'স' => 'sa',
        'হ' => 'ha',
        '০' => '0',
        '১' => '1',
        '২' => '2',
        '৩' => '3',
        '৪' => '4',
        '৫' => '5',
        '৬' => '6',
        '৭' => '7',
        '৮' => '8',
        '৯' => '9',
        'ক্ষ' => 'kso',
        'ষ্ণ' => 'sno',
        'জ্ঞ' => 'jno',
        'ঞ্জ' => 'nchho',
        'হ্ম' => 'hmo',
        'ঞ্চ' => 'ncho',
        'ঙ্ক' => 'ngko',
        'ট্ট' => 'tto',
        'ক্ষ্ম' => 'ksmo',
        'হ্ন' => 'hno',
        'হ্ণ' => 'hno',
        'ক্র' => 'kro',
        'গ্ধ' => 'gdho',
        'ত্র' => 'tro',
        'ক্ত' => 'kto',
        'ক্স' => 'kso',
        'ত্ত' => 'tto',
        'ত্ম' => 'tmo',
        'ক্ক' => 'kko',
        'ক্ম' => 'kmo',
        'ক্ল' => 'klo',
        'া' => 'a',
        'ি' => 'i',
        'ী' => 'ee',
        'ু' => 'o',
        'ূ' => 'u',
        'ৃ' => 'ri',
        'ৄ' => 'rii',
        'ে' => 'a',
        'ৈ' => 'ai',
        'ো' => 'o',
        'ৌ' => 'ow',
        '্য' => 'a',
        '্র' => 'r',
        'ঁ' => 'n',
        'ঃ' => 'oh',
        '়' => 'o',
        '্' => 'h',
        'ং' => 'ng',
        'ৢ' => 'n',
        'ৣ' => 'nn',
    ];

    /**
     * A conversion table for symbols that should be replaced after Unicode "marks" are removed from
     * transliterated strings.
     *
     * @var string[]
     */
    protected $normalizedConvertTable = [
        '&amp;' => 'and',
        '@' => 'at',
        '©' => 'c',
        '®' => 'r',
        'Æ' => 'ae',
        'Ø' => 'o',
        'ß' => 'ss',
        'æ' => 'ae',
        'ø' => 'o',
        'þ' => 'p',
        'Đ' => 'd',
        'đ' => 'd',
        'Ħ' => 'h',
        'ħ' => 'h',
        'ı' => 'i',
        'Ĳ' => 'ij',
        'ĳ' => 'ij',
        'ĸ' => 'k',
        'Ŀ' => 'l',
        'ŀ' => 'l',
        'Ł' => 'l',
        'ł' => 'l',
        'ŉ' => 'n',
        'Ŋ' => 'n',
        'ŋ' => 'n',
        'Œ' => 'oe',
        'œ' => 'oe',
        'Ŧ' => 't',
        'ŧ' => 't',
        'ſ' => 'z',
        'Ə' => 'e',
        'ƒ' => 'f',
        'ə' => 'e',
        'Є' => 'e',
        'І' => 'i',
        'А' => 'a',
        'Б' => 'b',
        'В' => 'v',
        'Г' => 'g',
        'Д' => 'd',
        'Е' => 'e',
        'Ж' => 'zh',
        'З' => 'z',
        'И' => 'i',
        'К' => 'k',
        'Л' => 'l',
        'М' => 'm',
        'Н' => 'n',
        'О' => 'o',
        'П' => 'p',
        'Р' => 'r',
        'С' => 's',
        'Т' => 't',
        'У' => 'u',
        'Ф' => 'f',
        'Х' => 'h',
        'Ц' => 'c',
        'Ч' => 'ch',
        'Ш' => 'sh',
        'Щ' => 'sch',
        'Ъ' => '-',
        'Ы' => 'y',
        'Ь' => '-',
        'Э' => 'je',
        'Ю' => 'ju',
        'Я' => 'ja',
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ъ' => '-',
        'ы' => 'y',
        'ь' => '-',
        'э' => 'je',
        'ю' => 'ju',
        'я' => 'ja',
        'є' => 'e',
        'і' => 'i',
        'Ґ' => 'g',
        'ґ' => 'g',
        'א' => 'a',
        'ב' => 'b',
        'ג' => 'g',
        'ד' => 'd',
        'ה' => 'h',
        'ו' => 'v',
        'ז' => 'z',
        'ח' => 'h',
        'ט' => 't',
        'י' => 'i',
        'ך' => 'k',
        'כ' => 'k',
        'ל' => 'l',
        'ם' => 'm',
        'מ' => 'm',
        'ן' => 'n',
        'נ' => 'n',
        'ס' => 's',
        'ע' => 'e',
        'ף' => 'p',
        'פ' => 'p',
        'ץ' => 'C',
        'צ' => 'c',
        'ק' => 'q',
        'ר' => 'r',
        'ש' => 'w',
        'ת' => 't',
        '™' => 'tm',
        'α' => 'a',
        'Α' => 'a',
        'β' => 'b',
        'Β' => 'b',
        'γ' => 'g',
        'Γ' => 'g',
        'δ' => 'd',
        'Δ' => 'd',
        'ε' => 'e',
        'Ε' => 'e',
        'ζ' => 'z',
        'Ζ' => 'z',
        'η' => 'i',
        'Η' => 'i',
        'θ' => 'th',
        'Θ' => 'th',
        'ι' => 'i',
        'Ι' => 'i',
        'κ' => 'k',
        'Κ' => 'k',
        'λ' => 'l',
        'Λ' => 'l',
        'μ' => 'm',
        'Μ' => 'm',
        'ν' => 'n',
        'Ν' => 'n',
        'ξ' => 'x',
        'Ξ' => 'x',
        'ο' => 'o',
        'Ο' => 'o',
        'π' => 'p',
        'Π' => 'p',
        'ρ' => 'r',
        'Ρ' => 'r',
        'σ' => 's',
        'ς' => 's',
        'Σ' => 's',
        'τ' => 't',
        'Τ' => 't',
        'υ' => 'u',
        'Υ' => 'y',
        'φ' => 'f',
        'Φ' => 'f',
        'χ' => 'ch',
        'Χ' => 'ch',
        'ψ' => 'ps',
        'Ψ' => 'ps',
        'ω' => 'o',
        'Ω' => 'o',
    ];

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $convertConfig = $config->getValue('url/convert', 'default');
        if ($convertConfig) {
            foreach ($convertConfig as $configValue) {
                $this->convertTable[(string)$configValue['from']] = (string)$configValue['to'];
            }
        }
    }

    /**
     * Returns a conversion table for symbols that should be replaced before Unicode "marks" are removed from
     * transliterated strings.
     *
     * This includes conversions from the system configuration.
     *
     * @return string[]
     */
    protected function getConvertTable()
    {
        return $this->convertTable;
    }

    /**
     * Returns a conversion table for symbols that should be replaced after Unicode "marks" are removed from
     * transliterated strings.
     *
     * @return string[]
     */
    protected function getNormalizedConvertTable()
    {
        return $this->normalizedConvertTable;
    }

    /**
     * Filter value
     *
     * @param string $string
     * @return string
     */
    public function filter($string)
    {
        $string = \Normalizer::normalize(
            strtr($string, $this->getConvertTable()),
            \Normalizer::FORM_D
        );

        $convertTable = $this->getNormalizedConvertTable();

        $string = preg_replace_callback(
            '/(([a-z])|.)\p{M}+/iu',
            function ($matches) use ($convertTable) {
                if (isset($matches[2])) {
                    // Preserve the original behavior of lowercasing accented uppercase characters.
                    return strtolower($matches[2]);
                } elseif (isset($convertTable[$matches[1]])) {
                    return $convertTable[$matches[1]];
                }

                return $matches[0];
            },
            $string
        );

        $string = strtr(
            \Normalizer::normalize($string, \Normalizer::FORM_C),
            $convertTable
        );

        return '"libiconv"' == ICONV_IMPL ? iconv(
            \Magento\Framework\Stdlib\StringUtils::ICONV_CHARSET,
            'ascii//ignore//translit',
            $string
        ) : $string;
    }
}
