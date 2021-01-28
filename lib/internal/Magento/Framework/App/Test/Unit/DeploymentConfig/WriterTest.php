<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\DeploymentConfig\CommentParser;
use Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Phrase;
use \PHPUnit\Framework\MockObject\MockObject as Mock;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Writer
     */
    private $object;

    /**
     * @var DeploymentConfig\Reader|Mock
     */
    private $reader;

    /**
     * @var WriteInterface|Mock
     */
    private $dirWrite;

    /**
     * @var ReadInterface|Mock
     */
    private $dirRead;

    /**
     * @var FormatterInterface|Mock
     */
    protected $formatter;

    /**
     * @var ConfigFilePool|Mock
     */
    private $configFilePool;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfig;

    /**
     * @var Filesystem|Mock
     */
    private $filesystem;

    /**
     * @var CommentParser|Mock
     */
    private $commentParserMock;

    protected function setUp(): void
    {
        $this->commentParserMock = $this->getMockBuilder(CommentParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formatter = $this->getMockForAbstractClass(FormatterInterface::class);
        $this->configFilePool = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dirWrite = $this->getMockForAbstractClass(WriteInterface::class);
        $this->dirRead = $this->getMockForAbstractClass(ReadInterface::class);

        $this->object = new Writer(
            $this->reader,
            $this->filesystem,
            $this->configFilePool,
            $this->deploymentConfig,
            $this->formatter,
            $this->commentParserMock
        );
    }

    public function testSaveConfig()
    {
        $configFiles = [
            ConfigFilePool::APP_CONFIG => 'config.php'
        ];
        $testSetExisting = [
            ConfigFilePool::APP_CONFIG => [
                'foo' => 'bar',
                'key' => 'value',
                'baz' => [
                    'test' => 'value',
                    'test1' => 'value1'
                ]
            ],
        ];
        $testSetUpdate = [
            ConfigFilePool::APP_CONFIG => [
                'baz' => [
                    'test' => 'value2'
                ]
            ],
        ];
        $testSetExpected = [
            ConfigFilePool::APP_CONFIG => [
                'foo' => 'bar',
                'key' => 'value',
                'baz' => [
                    'test' => 'value2',
                    'test1' => 'value1'
                ]
            ],
        ];
        $testComments = [
            'baz' => 'Baz comment2',
            'bar' => 'Bar comment'
        ];
        $existedComments = [
            'foo' => 'Foo comment',
            'baz' => 'Baz comment',
        ];
        $expectedComments = [
            'foo' => 'Foo comment',
            'baz' => 'Baz comment2',
            'bar' => 'Bar comment'
        ];

        $this->deploymentConfig->expects($this->once())
            ->method('resetData');
        $this->configFilePool->expects($this->once())
            ->method('getPaths')
            ->willReturn($configFiles);
        $this->dirWrite->expects($this->any())
            ->method('isExist')
            ->willReturn(true);
        $this->reader->expects($this->once())
            ->method('load')
            ->willReturn($testSetExisting[ConfigFilePool::APP_CONFIG]);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->with('config.php')
            ->willReturn($existedComments);
        $this->formatter->expects($this->once())
            ->method('format')
            ->with($testSetExpected[ConfigFilePool::APP_CONFIG], $expectedComments)
            ->willReturn([]);
        $this->dirWrite->expects($this->once())
            ->method('writeFile')
            ->with('config.php', []);
        $this->reader->expects($this->any())
            ->method('getFiles')
            ->willReturn('test.php');
        $this->dirRead->expects($this->any())
            ->method('getAbsolutePath');
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirWrite);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirRead);

        $this->object->saveConfig($testSetUpdate, false, null, $testComments);
    }

    public function testSaveConfigOverride()
    {
        $configFiles = [
            ConfigFilePool::APP_CONFIG => 'config.php'
        ];
        $testSetUpdate = [
            ConfigFilePool::APP_CONFIG => [
                'baz' => [
                    'test' => 'value2'
                ]
            ],
        ];
        $testSetExpected = [
            ConfigFilePool::APP_CONFIG => [
                'baz' => [
                    'test' => 'value2',
                ]
            ],
        ];

        $this->deploymentConfig->expects($this->once())
            ->method('resetData');
        $this->configFilePool->expects($this->once())
            ->method('getPaths')
            ->willReturn($configFiles);
        $this->dirWrite->expects($this->any())
            ->method('isExist')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->with('config.php')
            ->willReturn([]);
        $this->formatter->expects($this->once())
            ->method('format')
            ->with($testSetExpected[ConfigFilePool::APP_CONFIG])
            ->willReturn([]);
        $this->dirWrite->expects($this->once())
            ->method('writeFile')
            ->with('config.php', []);
        $this->reader->expects($this->any())
            ->method('getFiles')
            ->willReturn('test.php');
        $this->dirRead->expects($this->any())
            ->method('getAbsolutePath');
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirWrite);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirRead);

        $this->object->saveConfig($testSetUpdate, true);
    }

    /**
     */
    public function testSaveConfigException()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);
        $this->expectExceptionMessage('The "env.php" deployment config file isn\'t writable.');

        $exception = new FileSystemException(new Phrase('error when writing file config file'));

        $this->configFilePool->method('getPaths')
            ->willReturn([ConfigFilePool::APP_ENV => 'env.php']);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->with('env.php')
            ->willReturn([]);
        $this->dirWrite->method('writeFile')
            ->willThrowException($exception);
        $this->reader->expects($this->any())
            ->method('getFiles')
            ->willReturn('test.php');
        $this->dirRead->expects($this->any())
            ->method('getAbsolutePath');
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirWrite);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirRead);
        $this->dirWrite->expects($this->any())
            ->method('isExist')
            ->willReturn(true);

        $this->object->saveConfig([ConfigFilePool::APP_ENV => ['key' => 'value']]);
    }
}
