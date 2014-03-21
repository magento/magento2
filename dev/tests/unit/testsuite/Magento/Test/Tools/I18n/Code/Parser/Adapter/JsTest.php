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
namespace Magento\Test\Tools\I18n\Code\Parser\Adapter;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Tools\I18n\Code\Dictionary\Phrase;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var int
     */
    protected $_stringsCount;

    /**
     * @var \Magento\Tools\I18n\Code\Parser\Adapter\Js
     */
    protected $_adapter;

    protected function setUp()
    {
        // dev/tests/unit/testsuite/tools/I18n/Parser/Adapter/_files/file.js
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/file.js';
        $this->_stringsCount = count(file($this->_testFile));

        $this->_adapter = (new ObjectManager($this))->getObject('Magento\Tools\I18n\Code\Parser\Adapter\Js');
    }

    public function testParse()
    {
        $expectedResult = array(
            array(
                'phrase' => 'Phrase 1',
                'file' => $this->_testFile,
                'line' => $this->_stringsCount - 2,
                'quote' => Phrase::QUOTE_SINGLE
            ),
            array(
                'phrase' => 'Phrase 2 %1',
                'file' => $this->_testFile,
                'line' => $this->_stringsCount - 1,
                'quote' => Phrase::QUOTE_DOUBLE
            )
        );

        $this->_adapter->parse($this->_testFile);

        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }
}
