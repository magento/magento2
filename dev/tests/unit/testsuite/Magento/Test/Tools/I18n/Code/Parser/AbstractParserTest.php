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
namespace Magento\Test\Tools\I18n\Code\Parser;

class AbstractParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Code\Parser\AbstractParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_parserMock;

    protected function setUp()
    {
        $this->_parserMock = $this->getMockForAbstractClass(
            'Magento\Tools\I18n\Code\Parser\AbstractParser',
            array(),
            '',
            false
        );
    }

    /**
     * @param array $options
     * @param string $message
     * @dataProvider dataProviderForValidateOptions
     */
    public function testValidateOptions($options, $message)
    {
        $this->setExpectedException('InvalidArgumentException', $message);

        $this->_parserMock->addAdapter('php', $this->getMock('Magento\Tools\I18n\Code\Parser\AdapterInterface'));
        $this->_parserMock->parse($options);
    }

    public function dataProviderForValidateOptions()
    {
        return array(
            array(array(array('paths' => array())), 'Missed "type" in parser options.'),
            array(array(array('type' => '', 'paths' => array())), 'Missed "type" in parser options.'),
            array(
                array(array('type' => 'wrong_type', 'paths' => array())),
                'Adapter is not set for type "wrong_type".'
            ),
            array(array(array('type' => 'php')), '"paths" in parser options must be array.'),
            array(array(array('type' => 'php', 'paths' => '')), '"paths" in parser options must be array.')
        );
    }

    public function getPhrases()
    {
        $this->assertInternalType('array', $this->_parserMock->getPhrases());
    }
}
