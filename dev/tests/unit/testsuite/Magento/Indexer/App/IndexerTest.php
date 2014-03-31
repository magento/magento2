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
namespace Magento\Indexer\App;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\App\Indexer
     */
    protected $entryPoint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\Processor
     */
    protected $processorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Filesystem
     */
    protected $filesystemMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMock('Magento\Filesystem', array('getDirectoryWrite'), array(), '', false);
        $directoryMock = $this->getMock('Magento\Filesystem\Directory\Write', array(), array(), '', false);
        $directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($directoryMock)
        );
        $this->processorMock = $this->getMock('Magento\Indexer\Model\Processor', array(), array(), '', false);
        $this->entryPoint = new \Magento\Indexer\App\Indexer('reportDir', $this->filesystemMock, $this->processorMock);
    }

    public function testExecute()
    {
        $this->processorMock->expects($this->once())->method('reindexAll');
        $this->assertEquals('0', $this->entryPoint->launch());
    }
}
