<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\CommentParser;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class CommentParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var ConfigFilePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configFilePoolMock;

    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readDirectoryMock;

    /**
     * @var CommentParser
     */
    private $commentParser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->configFilePoolMock = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readDirectoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->readDirectoryMock);

        $this->commentParser = new CommentParser($this->filesystemMock, $this->configFilePoolMock);
    }

    public function testExecuteFileDoesNotExist()
    {
        $file = 'config.php';
        $expectedResult = [];

        $this->readDirectoryMock->expects($this->once())
            ->method('isExist')
            ->with($file)
            ->willReturn(false);

        $this->assertSame($expectedResult, $this->commentParser->execute($file));
    }

    public function testExecute()
    {
        $file = 'config.php';
        $content = <<<TEXT
<?php
return array (
  'ns1' => 
  array (
    's1' => 
    array (
      0 => 's11',
      1 => 's12',
    ),
    's2' => 
    array (
      0 => 's21',
      1 => 's22',
    ),
  ),
  /**
   * comment for namespace 2.
   * Next comment for' namespace 2
   */
  'ns2' => 
  array (
    's1' => 
    array (
      0 => 's11',
    ),
  ),
  // This comment will be ignored
  'ns3' => 'just text',
  /**
   * comment for namespace 4
   *     second line
   * For the section: ns4
   */
  'ns4' => 'just text',
  /**
   * For the section: ns5
   * *comment for namespace *5*
   */
  'ns5' => 'just text',
  # This comment will be ignored
  'ns6' => 'just text',
);

TEXT;

        $expectedResult = [
            'ns4' => "comment for namespace 4\n    second line",
            'ns5' => '*comment for namespace *5*',
        ];

        $this->readDirectoryMock->expects($this->once())
            ->method('isExist')
            ->with($file)
            ->willReturn(true);
        $this->readDirectoryMock->expects($this->once())
            ->method('readFile')
            ->with($file)
            ->willReturn($content);

        $this->assertEquals($expectedResult, $this->commentParser->execute($file));
    }
}
