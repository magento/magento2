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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Migration\System\Configuration;


require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Reader.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/FileManager.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Parser.php';
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Reader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_parserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mapperMock;

    protected function setUp()
    {
        $this->_fileManagerMock = $this->getMock(
            'Magento\Tools\Migration\System\FileManager',
            array(),
            array(),
            '',
            false
        );
        $this->_parserMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Parser',
            array(),
            array(),
            '',
            false
        );
        $this->_mapperMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Mapper',
            array(),
            array(),
            '',
            false
        );

        $this->_model = new \Magento\Tools\Migration\System\Configuration\Reader(
            $this->_fileManagerMock,
            $this->_parserMock,
            $this->_mapperMock
        );
    }

    public function testgetConfiguration()
    {
        $this->_fileManagerMock->expects(
            $this->once()
        )->method(
            'getFileList'
        )->will(
            $this->returnValue(array('testFile'))
        );
        $this->_fileManagerMock->expects(
            $this->once()
        )->method(
            'getContents'
        )->with(
            'testFile'
        )->will(
            $this->returnValue('<config><system><tabs></tabs></system></config>')
        );
        $parsedArray = array('config' => array('system' => array('tabs')));
        $this->_parserMock->expects(
            $this->once()
        )->method(
            'parse'
        )->with(
            $this->isInstanceOf('DOMDocument')
        )->will(
            $this->returnValue($parsedArray)
        );

        $transformedArray = array('value' => 'expected');
        $this->_mapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            $parsedArray
        )->will(
            $this->returnValue($transformedArray)
        );


        $this->assertEquals(array('testFile' => $transformedArray), $this->_model->getConfiguration());
    }
}
