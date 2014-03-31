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
 * @category    Magento
 * @package     Magento_Index
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Index\App;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Index\App\Indexer
     */
    protected $_entryPoint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Index\Model\IndexerFactory
     */
    protected $_indexFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_filesystem = $this->getMock('Magento\App\Filesystem', array('getDirectoryWrite'), array(), '', false);
        $directoryMock = $this->getMock('Magento\Filesystem\Directory\Write', array(), array(), '', false);
        $directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($directoryMock)
        );
        $this->_indexFactoryMock = $this->getMock(
            'Magento\Index\Model\IndexerFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_responseMock = $this->getMock('Magento\App\Console\Response', array(), array(), '', false);
        $this->_entryPoint = new \Magento\Index\App\Indexer(
            'reportDir',
            $this->_filesystem,
            $this->_indexFactoryMock,
            $this->_responseMock
        );
    }

    /**
     * @param bool $value
     * @dataProvider executeDataProvider
     */
    public function testLaunch($value)
    {
        $process = $this->getMock(
            'Magento\Index\Model\Process',
            array('getIndexer', 'reindexEverything', '__wakeup'),
            array(),
            '',
            false
        );
        $indexer = $this->getMock('Magento\Index\Model\Indexer', array('getProcessesCollection'), array(), '', false);
        $indexerInterface = $this->getMock('Magento\Index\Model\IndexerInterface');
        $this->_indexFactoryMock->expects($this->once())->method('create')->will($this->returnValue($indexer));
        $indexer->expects($this->once())->method('getProcessesCollection')->will($this->returnValue(array($process)));
        $process->expects($this->any())->method('getIndexer')->will($this->returnValue($indexerInterface));

        if ($value) {
            $indexerInterface->expects($this->once())->method('isVisible')->will($this->returnValue(true));
            $process->expects($this->once())->method('reindexEverything');
        } else {
            $indexerInterface->expects($this->once())->method('isVisible')->will($this->returnValue(false));
            $process->expects($this->never())->method('reindexEverything');
        }
        $this->assertEquals($this->_responseMock, $this->_entryPoint->launch());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return array(array(true), array(false));
    }
}
