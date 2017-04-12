<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests, that perform search of words, that signal of obsolete code
 */
namespace Magento\Test\Legacy;

use Magento\Framework\Component\ComponentRegistrar;

class WordsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Inspection\WordsFinder
     */
    protected static $_wordsFinder;

    public static function setUpBeforeClass()
    {
        self::$_wordsFinder = new \Magento\TestFramework\Inspection\WordsFinder(
            glob(__DIR__ . '/_files/words_*.xml'),
            BP,
            new ComponentRegistrar()
        );
    }

    public function testWords()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $words = self::$_wordsFinder->findWords(realpath($file));
                if ($words) {
                    $this->fail("Found words: '" . implode("', '", $words) . "' in '{$file}' file");
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getAllFiles()
        );
    }
}
