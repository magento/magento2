<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Locale\Data;

class Language implements ListInterface
{
    /**
     * @var array
     */
    protected $data = [
        'af' => 'Afrikaans',
        'ar' => 'Arabic',
        'az' => 'Azerbaijani',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bn' => 'Bengali',
        'bs' => 'Bosnian',
        'ca' => 'Catalan',
        'cs' => 'Czech',
        'cy' => 'Welsh',
        'da' => 'Danish',
        'de_AT' => 'Austrian German',
        'de_CH' => 'Swiss High German',
        'de' => 'German',
        'el' => 'Greek',
        'en_AU' => 'Australian English',
        'en_CA' => 'Canadian English',
        'en_GB' => 'British English',
        'en' => 'English',
        'en_US' => 'U.S. English',
        'es' => 'Spanish',
        'es_ES' => 'Iberian Spanish',
        'et' => 'Estonian',
        'fa' => 'Persian',
        'fi' => 'Finnish',
        'fil' => 'Filipino',
        'fr_CA' => 'Canadian French',
        'fr' => 'French',
        'gl' => 'Galician',
        'gu' => 'Gujarati',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hr' => 'Croatian',
        'hu' => 'Hungarian',
        'id' => 'Indonesian',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ka' => 'Georgian',
        'km' => 'Khmer',
        'ko' => 'Korean',
        'lo' => 'Lao',
        'lt' => 'Lithuanian',
        'lv' => 'Latvian',
        'mk' => 'Macedonian',
        'mn' => 'Mongolian',
        'ms' => 'Malay',
        'nb' => 'Norwegian BokmÃ¥l',
        'nl' => 'Dutch',
        'nn' => 'Norwegian Nynorsk',
        'pl' => 'Polish',
        'pt_BR' => 'Brazilian Portuguese',
        'pt_PT' => 'Iberian Portuguese',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'sq' => 'Albanian',
        'sr' => 'Serbian',
        'sv' => 'Swedish',
        'sw' => 'Swahili',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'vi' => 'Vietnamese',
        'zh' => 'Chinese',
    ];

    /**
     * Retrieve list of languages
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
} 