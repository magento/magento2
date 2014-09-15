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
    protected $processor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $filesystem;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', ['getDirectoryWrite'], [], '', false);
        $this->processor = $this->getMock('Magento\Indexer\Model\Processor', [], [], '', false);
        $this->entryPoint = new Indexer('reportDir', $this->filesystem, $this->processor);
    }

    public function testExecute()
    {
        $dir = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $dir->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($dir));
        $this->processor->expects($this->once())->method('reindexAll');
        $this->assertEquals('0', $this->entryPoint->launch());
    }

    public function testCatchException()
    {
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        $this->assertFalse($this->entryPoint->catchException($bootstrap, new \Exception));
    }
}
