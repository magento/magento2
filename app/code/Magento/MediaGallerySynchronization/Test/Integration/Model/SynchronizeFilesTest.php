<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Integration\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for SynchronizeFiles.
 */
class SynchronizeFilesTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var SynchronizeFilesInterface
     */
    private $synchronizeFiles;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPath;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
        $this->synchronizeFiles = Bootstrap::getObjectManager()->get(SynchronizeFilesInterface::class);
        $this->getAssetsByPath = Bootstrap::getObjectManager()->get(GetAssetsByPathsInterface::class);
        $this->mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Test for SynchronizeFiles::execute
     *
     * @dataProvider filesProvider
     * @param string $file
     * @param string $title
     * @param string $source
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function testExecute(
        string $file,
        string $title,
        string $source
    ): void {
        $path = realpath(__DIR__ . '/../_files/' . $file);
        $modifiableFilePath = $this->mediaDirectory->getAbsolutePath($file);
        $this->driver->copy(
            $path,
            $modifiableFilePath
        );

        $this->synchronizeFiles->execute([$file]);

        $loadedAsset = $this->getAssetsByPath->execute([$file])[0];

        $this->assertEquals($title, $loadedAsset->getTitle());
        $this->assertEquals($source, $loadedAsset->getSource());

        $this->driver->deleteFile($modifiableFilePath);
    }

    /**
     * Data provider for testExecute
     *
     * @return array[]
     */
    public function filesProvider(): array
    {
        return [
            [
                '/magento.jpg',
                'magento',
                'Local'
            ]
        ];
    }
}
