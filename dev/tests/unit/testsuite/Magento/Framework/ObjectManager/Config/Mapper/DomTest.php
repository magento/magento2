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
namespace Magento\Framework\ObjectManager\Config\Mapper;

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Config\Mapper\Dom
     */
    protected $_mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentInterpreter;

    protected function setUp()
    {
        $argumentParser = $this->getMock('\Magento\Framework\ObjectManager\Config\Mapper\ArgumentParser');
        $argumentParser->expects(
            $this->any()
        )->method(
            'parse'
        )->will(
            $this->returnCallback(array($this, 'parserMockCallback'))
        );

        $booleanUtils = $this->getMock('\Magento\Framework\Stdlib\BooleanUtils');
        $booleanUtils->expects(
            $this->any()
        )->method(
            'toBoolean'
        )->will(
            $this->returnValueMap(array(array('true', true), array('false', false)))
        );

        $this->argumentInterpreter = $this->getMock('Magento\Framework\Data\Argument\InterpreterInterface');
        $this->argumentInterpreter->expects(
            $this->any()
        )->method(
            'evaluate'
        )->with(
            array('xsi:type' => 'string', 'value' => 'test value')
        )->will(
            $this->returnValue('test value')
        );
        $this->_mapper = new Dom($this->argumentInterpreter, $booleanUtils, $argumentParser);
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $xmlFile = __DIR__ . '/_files/simple_di_config.xml';
        $dom->loadXML(file_get_contents($xmlFile));

        $resultFile = __DIR__ . '/_files/mapped_simple_di_config.php';
        $expectedResult = include $resultFile;
        $this->assertEquals($expectedResult, $this->_mapper->convert($dom));
    }

    /**
     * Callback for mocking parse() method of the argument parser
     *
     * @param \DOMElement $argument
     * @return string
     */
    public function parserMockCallback(\DOMElement $argument)
    {
        $this->assertNotEmpty($argument->getAttribute('name'));
        $this->assertNotEmpty($argument->getAttribute('xsi:type'));
        return array('xsi:type' => 'string', 'value' => 'test value');
    }

    /**
     * @param string $xmlData
     * @dataProvider wrongXmlDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid application config. Unknown node: wrong_node.
     */
    public function testMapThrowsExceptionWhenXmlHasWrongFormat($xmlData)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlData);
        $this->_mapper->convert($dom);
    }

    /**
     * @return array
     */
    public function wrongXmlDataProvider()
    {
        return array(
            array(
                '<?xml version="1.0"?><config><type name="some_type">' .
                '<wrong_node name="wrong_node" />' .
                '</type></config>'
            ),
            array(
                '<?xml version="1.0"?><config><virtualType name="some_type">' .
                '<wrong_node name="wrong_node" />' .
                '</virtualType></config>'
            ),
            array(
                '<?xml version="1.0"?><config>' .
                '<preference for="some_interface" type="some_class" />' .
                '<wrong_node name="wrong_node" />' .
                '</config>'
            )
        );
    }
}
