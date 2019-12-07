<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Remove accents from string
 */
class RemoveAccents implements \Zend_Filter_Interface
{
    /**
     * @var bool
     */
    protected $german;

    /**
     * @param bool $german
     */
    public function __construct($german = false)
    {
        $this->german = $german;
    }

    /**
     * @param string $string
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function filter($string)
    {
        static $replacements;

        if (empty($replacements[$this->german])) {
            $substitutions = [
                // single ISO-8859-1 letters
                192 => 'A',
                193 => 'A',
                194 => 'A',
                195 => 'A',
                196 => 'A',
                197 => 'A',
                199 => 'C',
                208 => 'D',
                200 => 'E',
                201 => 'E',
                202 => 'E',
                203 => 'E',
                204 => 'I',
                205 => 'I',
                206 => 'I',
                207 => 'I',
                209 => 'N',
                210 => 'O',
                211 => 'O',
                212 => 'O',
                213 => 'O',
                214 => 'O',
                216 => 'O',
                138 => 'S',
                217 => 'U',
                218 => 'U',
                219 => 'U',
                220 => 'U',
                221 => 'Y',
                142 => 'Z',
                224 => 'a',
                225 => 'a',
                226 => 'a',
                227 => 'a',
                228 => 'a',
                229 => 'a',
                231 => 'c',
                232 => 'e',
                233 => 'e',
                234 => 'e',
                235 => 'e',
                236 => 'i',
                237 => 'i',
                238 => 'i',
                239 => 'i',
                241 => 'n',
                240 => 'o',
                242 => 'o',
                243 => 'o',
                244 => 'o',
                245 => 'o',
                246 => 'o',
                248 => 'o',
                154 => 's',
                249 => 'u',
                250 => 'u',
                251 => 'u',
                252 => 'u',
                253 => 'y',
                255 => 'y',
                158 => 'z',
                // HTML entities
                258 => 'A',
                260 => 'A',
                262 => 'C',
                268 => 'C',
                270 => 'D',
                272 => 'D',
                280 => 'E',
                282 => 'E',
                286 => 'G',
                304 => 'I',
                313 => 'L',
                317 => 'L',
                321 => 'L',
                323 => 'N',
                327 => 'N',
                336 => 'O',
                340 => 'R',
                344 => 'R',
                346 => 'S',
                350 => 'S',
                354 => 'T',
                356 => 'T',
                366 => 'U',
                368 => 'U',
                377 => 'Z',
                379 => 'Z',
                259 => 'a',
                261 => 'a',
                263 => 'c',
                269 => 'c',
                271 => 'd',
                273 => 'd',
                281 => 'e',
                283 => 'e',
                287 => 'g',
                305 => 'i',
                322 => 'l',
                314 => 'l',
                318 => 'l',
                324 => 'n',
                328 => 'n',
                337 => 'o',
                341 => 'r',
                345 => 'r',
                347 => 's',
                351 => 's',
                357 => 't',
                355 => 't',
                367 => 'u',
                369 => 'u',
                378 => 'z',
                380 => 'z',
                // ligatures
                198 => 'Ae',
                230 => 'ae',
                140 => 'Oe',
                156 => 'oe',
                223 => 'ss',
            ];

            if ($this->german) {
                // umlauts
                $germanReplacements = [
                    196 => 'Ae',
                    228 => 'ae',
                    214 => 'Oe',
                    246 => 'oe',
                    220 => 'Ue',
                    252 => 'ue',
                ];
                $substitutions = $germanReplacements + $substitutions;
            }

            $replacements[$this->german] = [];
            foreach ($substitutions as $code => $value) {
                $replacements[$this->german][$code < 256 ? chr($code) : '&#' . $code . ';'] = $value;
            }
        }

        // convert string from default database format (UTF-8)
        // to encoding which replacement arrays made with (ISO-8859-1)
        $convertedString = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $string);
        if ($convertedString) {
            $string = $convertedString;
        }
        // Replace
        $string = strtr($string, $replacements[$this->german]);
        return $string;
    }
}
