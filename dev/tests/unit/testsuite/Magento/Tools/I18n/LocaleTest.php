<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n;


class LocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Target locale is system default locale.
     */
    public function testLocaleIsSystemDefaultLocaleException()
    {
        new Locale('en_US');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Target locale must match the following format: "aa_AA".
     */
    public function testWrongLocaleFormatException()
    {
        new Locale('wrong_locale');
    }

    public function testToStringConvert()
    {
        $locale = new Locale('de_DE');

        $this->assertEquals('de_DE', (string)$locale);
    }
}
