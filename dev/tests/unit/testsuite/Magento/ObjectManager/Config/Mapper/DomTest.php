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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager\Config\Mapper;

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager\Config\Mapper\Dom
     */
    protected $_mapper;

    protected function setUp()
    {
        $this->_mapper = new \Magento\ObjectManager\Config\Mapper\Dom();
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $xmlFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'simple_di_config.xml';
        $dom->loadXML(file_get_contents($xmlFile));

        $resultFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'mapped_simple_di_config.php';
        $expectedResult = include $resultFile;
        $this->assertEquals($expectedResult, $this->_mapper->convert($dom));
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
                '<?xml version="1.0"?><config><type name="some_type">'
                    . '<wrong_node name="wrong_node" />'
                    . '</type></config>',
            ),
            array(
                '<?xml version="1.0"?><config><type name="some_type">'
                    . '<param name="some_param"><wrong_node name="wrong_node" /></param>'
                    . '</type></config>',
            ),
            array(
                '<?xml version="1.0"?><config>'
                    . '<preference for="some_interface" type="some_class" />'
                    . '<wrong_node name="wrong_node" />'
                    . '</config>',
            ),
        );
    }
}
