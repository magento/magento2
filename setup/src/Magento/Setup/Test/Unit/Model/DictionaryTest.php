<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\Dictionary;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    /**
     * @var array
     */
    private $dictionary = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing',
        'elit', 'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore',
        'et', 'dolore', 'magna', 'aliqua'
    ];

    public function testDictionaryFileNotFoundException()
    {
        $this->expectException('Magento\Setup\Exception');
        $this->expectExceptionMessage('Description file some-wrong-file.csv not found or is not readable');
        $dictionary = new Dictionary('some-wrong-file.csv');
        $dictionary->getRandWord();
    }

    public function testDictionaryFileIsEmptyException()
    {
        $this->expectException('Magento\Setup\Exception');
        $this->expectExceptionMessageMatches('/Dictionary file .*empty-dictionary\.csv is empty/');
        $filePath = __DIR__ . '/_files/empty-dictionary.csv';
        file_put_contents($filePath, '');

        try {
            $dictionary = new Dictionary($filePath);
            $dictionary->getRandWord();
        } finally {
            unlink($filePath);
        }
    }

    public function testGetRandWord()
    {
        $filePath = __DIR__ . '/_files/valid-dictionary.csv';
        file_put_contents($filePath, implode(PHP_EOL, $this->dictionary));

        $dictionary = new Dictionary($filePath);

        $this->assertContains($dictionary->getRandWord(), $this->dictionary);
        $this->assertContains($dictionary->getRandWord(), $this->dictionary);
        $this->assertContains($dictionary->getRandWord(), $this->dictionary);

        unlink($filePath);
    }
}
