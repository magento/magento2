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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests, that perform search of words, that signal of obsolete code
 */
class Legacy_WordsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Inspection_WordsFinder
     */
    protected static $_wordsFinder;

    public static function setUpBeforeClass()
    {
        self::$_wordsFinder = new Inspection_WordsFinder(
            glob(__DIR__ . '/_files/words_*.xml'),
            Utility_Files::init()->getPathToSource()
        );
    }

    /**
     * @param string $file
     * @dataProvider wordsDataProvider
     */
    public function testWords($file)
    {
        $words = self::$_wordsFinder->findWords($file);
        if ($words) {
            $this->fail("Found words: '" . implode("', '", $words) . "' in '$file' file");
        }
    }

    /**
     * @return array
     */
    public function wordsDataProvider()
    {
        return Utility_Files::init()->getAllFiles();
    }
}
