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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Resource\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Resource\Config\Reader
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_schemaLocatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configLocalMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validationStateMock;

    protected function setUp()
    {
        $this->_filePath = __DIR__ . '/_files' . DIRECTORY_SEPARATOR;

        $this->_fileResolverMock = $this->getMock('Magento\Config\FileResolverInterface');
        $this->_validationStateMock = $this->getMock('Magento\Config\ValidationStateInterface');
        $this->_schemaLocatorMock = $this->getMock('Magento\Core\Model\Resource\Config\SchemaLocator',
            array(), array(), '', false);

        $this->_converterMock = $this->getMock(
            'Magento\Core\Model\Resource\Config\Converter', array(), array(), '', false
        );

        $this->_configLocalMock = $this->getMock(
            'Magento\Core\Model\Config\Local', array(), array(), '', false
        );

        $this->_model = new \Magento\Core\Model\Resource\Config\Reader(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            $this->_configLocalMock,
            'cacheId'
        );
    }

    /**
     * @covers \Magento\Core\Model\Resource\Config\Reader::read
     */
    public function testRead()
    {
        $localConfig = array(
            'defaultSetup' => array(
                'name' => 'defaultSetup',
                'connection' => 'defaultConnection'
            )
        );

        $modulesConfig = include ($this->_filePath . 'resources.php');

        $expectedResult = array(
            'resourceName' => array(
                'name' => 'resourceName',
                'extends' => 'anotherResourceName',
            ),
            'otherResourceName' => array(
                'name' => 'otherResourceName',
                'connection' => 'connectionName',
            ),
            'defaultSetup' => array(
                'name' => 'defaultSetup',
                'connection' => 'defaultConnection'
            ),
        );

        $this->_configLocalMock->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue($localConfig));

        $this->_fileResolverMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array($this->_filePath .  'resources.xml')));

        $this->_converterMock->expects($this->once())
            ->method('convert')
            ->will($this->returnValue($modulesConfig));

        $this->assertEquals($expectedResult, $this->_model->read());
    }
}
