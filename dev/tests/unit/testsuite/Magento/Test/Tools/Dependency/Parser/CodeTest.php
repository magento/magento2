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
namespace Magento\Test\Tools\Dependency\Parser;

use Magento\TestFramework\Helper\ObjectManager;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\Parser\Code
     */
    protected $parser;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->parser = $objectManagerHelper->getObject('Magento\Tools\Dependency\Parser\Code');
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parse error: Option "files_for_parse" is wrong.
     * @dataProvider dataProviderWrongOptionFilesForParse
     */
    public function testParseWithWrongOptionFilesForParse($options)
    {
        $this->parser->parse($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionFilesForParse()
    {
        return array(
            array(array('files_for_parse' => array(), 'declared_namespaces' => array(1, 2))),
            array(array('files_for_parse' => 'sting', 'declared_namespaces' => array(1, 2))),
            array(array('there_are_no_files_for_parse' => array(1, 3), 'declared_namespaces' => array(1, 2)))
        );
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parse error: Option "declared_namespaces" is wrong.
     * @dataProvider dataProviderWrongOptionDeclaredNamespace
     */
    public function testParseWithWrongOptionDeclaredNamespace($options)
    {
        $this->parser->parse($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionDeclaredNamespace()
    {
        return array(
            array(array('declared_namespaces' => array(), 'files_for_parse' => array(1, 2))),
            array(array('declared_namespaces' => 'sting', 'files_for_parse' => array(1, 2))),
            array(array('there_are_no_declared_namespaces' => array(1, 3), 'files_for_parse' => array(1, 2)))
        );
    }
}
