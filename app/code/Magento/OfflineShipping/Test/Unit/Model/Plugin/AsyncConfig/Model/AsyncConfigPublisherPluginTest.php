<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Plugin\AsyncConfig\Model;

use Magento\AsyncConfig\Model\AsyncConfigPublisher;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Math\Random;
use Magento\OfflineShipping\Model\Plugin\AsyncConfig\Model\AsyncConfigPublisherPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AsyncConfigPublisherPluginTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private Filesystem $filesystem;

    /**
     * @var Random|MockObject
     */
    private Random $rand;

    /**
     * @var RequestFactory|MockObject
     */
    private RequestFactory $requestFactory;

    /**
     * @var AsyncConfigPublisherPlugin
     */
    private AsyncConfigPublisherPlugin $plugin;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->rand = $this->createMock(Random::class);
        $this->requestFactory = $this->createMock(RequestFactory::class);
        $this->plugin = new AsyncConfigPublisherPlugin($this->filesystem, $this->rand, $this->requestFactory);

        parent::setUp();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testBeforeSaveConfigDataNoImportFile(): void
    {
        $request = $this->createMock(Http::class);
        $request->expects($this->once())->method('getFiles')->willReturn([]);
        $this->requestFactory->expects($this->once())->method('create')->willReturn($request);

        $subject = $this->createMock(AsyncConfigPublisher::class);
        $params = ['test'];
        $this->assertSame([$params], $this->plugin->beforeSaveConfigData($subject, $params));
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testBeforeSaveConfigDataException(): void
    {
        $files['groups']['tablerate']['fields']['import']['value'] = [
            'tmp_name' => 'some/path/to/file/import.csv',
            'name' => 'import.csv'
        ];
        $request = $this->createMock(Http::class);
        $request->expects($this->once())->method('getFiles')->willReturn($files);
        $this->requestFactory->expects($this->once())->method('create')->willReturn($request);
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('copy')->willReturn(false);
        $varDir = $this->createMock(WriteInterface::class);
        $varDir->expects($this->once())->method('getDriver')->willReturn($driver);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_IMPORT_EXPORT)
            ->willReturn($varDir);
        $this->rand->expects($this->once())->method('getRandomString')->willReturn('123456');

        $this->expectException(FileSystemException::class);
        $subject = $this->createMock(AsyncConfigPublisher::class);
        $config['groups']['tablerate']['fields']['import']['value']['name'] = 'import.csv';
        $this->plugin->beforeSaveConfigData($subject, $config);
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testBeforeSaveConfigDataSuccess(): void
    {
        $files['groups']['tablerate']['fields']['import']['value'] = [
            'tmp_name' => 'some/path/to/file/import.csv',
            'name' => 'import.csv'
        ];
        $request = $this->createMock(Http::class);
        $request->expects($this->once())->method('getFiles')->willReturn($files);
        $this->requestFactory->expects($this->once())->method('create')->willReturn($request);
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('copy')->willReturn(true);
        $varDir = $this->createMock(WriteInterface::class);
        $varDir->expects($this->once())->method('getDriver')->willReturn($driver);
        $varDir->expects($this->exactly(2))->method('getAbsolutePath')->willReturn('some/path/to/file');
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_IMPORT_EXPORT)
            ->willReturn($varDir);
        $this->rand->expects($this->once())->method('getRandomString')->willReturn('123456');

        $subject = $this->createMock(AsyncConfigPublisher::class);
        $config['groups']['tablerate']['fields']['import']['value']['name'] = 'import.csv';
        $files['groups']['tablerate']['fields']['import']['value']['name'] = '123456_import.csv';
        $result['groups']['tablerate']['fields']['import']['value'] = [
            'name' => '123456_import.csv',
            'full_path' => 'some/path/to/file'
        ];
        $this->assertSame([$result], $this->plugin->beforeSaveConfigData($subject, $config));
    }
}
