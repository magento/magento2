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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure\Mapper\Helper;

class RelativePathConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Mapper\Helper\RelativePathConverter
     */
    protected $_sut;

    protected function setUp()
    {
        $this->_sut = new \Magento\Backend\Model\Config\Structure\Mapper\Helper\RelativePathConverter();
    }

    public function testConvertWithInvalidRelativePath()
    {
        $nodePath = 'node/path';
        $relativePath = '*/*/*/relativePath';

        $exceptionMessage = sprintf('Invalid relative path %s in %s node', $relativePath, $nodePath);

        $this->setExpectedException('InvalidArgumentException', $exceptionMessage);
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider testConvertWithInvalidArgumentsDataProvider
     * @param string $nodePath
     * @param string $relativePath
     */
    public function testConvertWithInvalidArguments($nodePath, $relativePath)
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid arguments');
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider testConvertDataProvider
     * @param string $nodePath
     * @param string $relativePath
     * @param string $result
     */
    public function testConvert($nodePath, $relativePath, $result)
    {
        $this->assertEquals($result, $this->_sut->convert($nodePath, $relativePath));
    }

    public function testConvertWithInvalidArgumentsDataProvider()
    {
        return array(array('', ''), array('some/node', ''), array('', 'some/node'));
    }

    public function testConvertDataProvider()
    {
        return array(
            array('currentNode', 'relativeNode', 'relativeNode'),
            array('current/node/path', 'relative/node/path', 'relative/node/path'),
            array('current/node', 'siblingRelativeNode', 'current/siblingRelativeNode'),
            array('current/node', '*/siblingNode', 'current/siblingNode'),
            array('very/deep/node/hierarchy', '*/*/sourceNode', 'very/deep/sourceNode')
        );
    }
}
