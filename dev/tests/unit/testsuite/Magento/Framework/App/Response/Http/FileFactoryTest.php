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
namespace Magento\Framework\App\Response\Http;

class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileSystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_dirMock;

    protected function setUp()
    {
        $this->_fileSystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryWrite'),
            array(),
            '',
            false
        );
        $this->_dirMock = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\Write'
        )->disableOriginalConstructor()->getMock();

        $this->_fileSystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->withAnyParameters()->will(
            $this->returnValue($this->_dirMock)
        );


        $this->_fileSystemMock->expects(
            $this->any()
        )->method(
            'isFile'
        )->withAnyParameters()->will(
            $this->returnValue(0)
        );
        $this->_responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['setHeader', 'sendHeaders', '__wakeup'],
            [],
            '',
            false
        );
        $this->_responseMock->expects(
            $this->any()
        )->method(
            'setHeader'
        )->will(
            $this->returnValue($this->_responseMock)
        );
        $this->_model = new \Magento\Framework\App\Response\Http\FileFactory(
            $this->_responseMock,
            $this->_fileSystemMock
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateIfContentDoesntHaveRequiredKeys()
    {
        $this->_model->create('fileName', array());
    }

    /**
     * @expectedException \Exception
     * @exceptedExceptionMessage File not found
     */
    public function testCreateIfFileNotExist()
    {
        $file = 'some_file';
        $content = array('type' => 'filename', 'value' => $file);

        $this->_model->create('fileName', $content);
    }
}
