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
namespace Magento\Core\Model\File\Storage;

class SynchronizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\File\Storage\Synchronization
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_streamMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_streamFactoryMock;

    /**
     * @var string
     */
    protected $_content = 'content';

    protected function setUp()
    {
        $this->_storageFactoryMock =
            $this->getMock('Magento\Core\Model\File\Storage\DatabaseFactory', array('create'), array(), '', false);
        $this->_storageMock = $this->getMock('Magento\Core\Model\File\Storage\Database',
                array('getContent', 'getId', 'loadByFilename'), array(), '', false);
        $this->_storageFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->_storageMock));

        $this->_storageMock->expects($this->once())->method('getContent')->will($this->returnValue($this->_content));
        $this->_streamFactoryMock =
            $this->getMock('Magento\Filesystem\Stream\LocalFactory', array('create'), array(), '', false);
        $this->_streamMock = $this->getMock('Magento\Filesystem\StreamInterface');
        $this->_streamFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_streamMock));

        $this->_model = new \Magento\Core\Model\File\Storage\Synchronization(
                        $this->_storageFactoryMock, $this->_streamFactoryMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_storageMock);
    }

    public function testSynchronize()
    {
        $relativeFileName = 'config.xml';
        $filePath = realpath(__DIR__ . '/_files/');
        $this->_storageMock->expects($this->once())->method('getId')->will($this->returnValue(true));
        $this->_storageMock->expects($this->once())->method('loadByFilename');
        $this->_streamMock->expects($this->once())->method('open')->with('w');
        $this->_streamMock->expects($this->once())->method('write')->with($this->_content);
        $this->_model->synchronize($relativeFileName, $filePath);
    }
}
