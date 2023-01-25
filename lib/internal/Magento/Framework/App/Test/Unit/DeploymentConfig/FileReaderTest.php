<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 */
class FileReaderTest extends TestCase
{
    /**
     * @var FileReader
     */
    private $model;

    /**
     * @var DirectoryList|Mock
     */
    private $dirListMock;

    /**
     * @var DriverPool|Mock
     */
    private $driverPoolMock;

    /**
     * @var ConfigFilePool|Mock
     */
    private $configFilePool;

    /**
     * @var DriverInterface|Mock
     */
    private $driverMock;

    protected function setUp(): void
    {
        $this->dirListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->driverPoolMock = $this->getMockBuilder(DriverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFilePool = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->driverMock = $this->getMockBuilder(DriverInterface::class)
            ->getMockForAbstractClass();

        $this->model = new FileReader(
            $this->dirListMock,
            $this->driverPoolMock,
            $this->configFilePool
        );
    }

    public function testLoad()
    {
        $fileKey = 'configKeyOne';

        $this->dirListMock->expects($this->exactly(2))
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $this->driverPoolMock->expects($this->exactly(2))
            ->method('getDriver')
            ->with(DriverPool::FILE)
            ->willReturn($this->driverMock);
        $this->configFilePool->expects($this->exactly(2))
            ->method('getPath')
            ->willReturnMap([['configKeyOne', 'config.php']]);
        $this->driverMock->expects($this->exactly(2))
            ->method('isExists')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->configFilePool
            ->expects($this->any())
            ->method('getPath')
            ->willReturnMap([['configKeyOne', 'config.php']]);

        $this->assertSame(['fooKey' => 'foo', 'barKey' => 'bar'], $this->model->load($fileKey));
        $this->assertSame([], $this->model->load($fileKey));
    }
}
