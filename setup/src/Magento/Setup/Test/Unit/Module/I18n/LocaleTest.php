<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

use \Magento\Setup\Module\I18n\Locale;

class LocaleTest extends \PHPUnit_Framework_TestCase
{
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
