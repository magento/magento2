<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $dictionary = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing',
        'elit', 'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore',
        'et', 'dolore', 'magna', 'aliqua'
    ];

    /**
     * @expectedException        \Magento\Setup\Exception
     * @expectedExceptionMessage Description file some-wrong-file.csv not found or is not readable
     */
    public function testDictionaryFileNotFoundException()
    {
        $dictionary = new \Magento\Setup\Model\Dictionary('some-wrong-file.csv');
        $dictionary->getRandWord();
    }

    /**
     * @expectedException        \Magento\Setup\Exception
     * @expectedExceptionMessageRegExp /Dictionary file .*empty-dictionary\.csv is empty/
     */
    public function testDictionaryFileIsEmptyException()
    {
        $filePath = __DIR__ . '/_files/empty-dictionary.csv';
        file_put_contents($filePath, '');

        try {
            $dictionary = new \Magento\Setup\Model\Dictionary($filePath);
            $dictionary->getRandWord();
        } finally {
            unlink($filePath);
        }
    }

    public function testGetRandWord()
    {
        $filePath = __DIR__ . '/_files/valid-dictionary.csv';
        file_put_contents($filePath, implode(PHP_EOL, $this->dictionary));

        $dictionary = new \Magento\Setup\Model\Dictionary($filePath);

        $this->assertTrue(in_array($dictionary->getRandWord(), $this->dictionary));
        $this->assertTrue(in_array($dictionary->getRandWord(), $this->dictionary));
        $this->assertTrue(in_array($dictionary->getRandWord(), $this->dictionary));

        unlink($filePath);
    }
}
