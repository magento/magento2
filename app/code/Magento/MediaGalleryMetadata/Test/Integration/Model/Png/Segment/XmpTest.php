<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Test\Integration\Model\Png\Segment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\MediaGalleryMetadata\Model\Png\Segment\WriteXmp;
use Magento\MediaGalleryMetadata\Model\Png\Segment\ReadXmp;
use Magento\MediaGalleryMetadata\Model\Png\ReadFile;
use Magento\MediaGalleryMetadata\Model\MetadataFactory;

/**
 * Test for Xmp reader and writer
 */
class XmpTest extends TestCase
{
    /**
     * @var WriteXmp
     */
    private $xmpWriter;

    /**
     * @var ReadXmp
     */
    private $xmpReader;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var ReadFile
     */
    private $fileReader;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->varDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->xmpWriter = Bootstrap::getObjectManager()->get(WriteXmp::class);
        $this->xmpReader = Bootstrap::getObjectManager()->get(ReadXmp::class);
        $this->fileReader = Bootstrap::getObjectManager()->get(ReadFile::class);
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
        $this->metadataFactory = Bootstrap::getObjectManager()->get(MetadataFactory::class);
    }

    /**
     * Test for Xmp reader and writer
     *
     * @dataProvider filesProvider
     * @param string $fileName
     * @param string $title
     * @param string $description
     * @param array $keywords
     * @throws LocalizedException
     */
    public function testWriteRead(
        string $fileName,
        string $title,
        string $description,
        array $keywords
    ): void {
        $path = realpath(__DIR__ . '/../../../../_files/' . $fileName);
        $modifiableFilePath = $this->varDirectory->getAbsolutePath($fileName);
        $this->driver->copy(
            $path,
            $modifiableFilePath
        );
        $modifiableFilePath = $this->fileReader->execute($modifiableFilePath);
        $originalMetadata = $this->xmpReader->execute($modifiableFilePath);

        $this->assertEmpty($originalMetadata->getTitle());
        $this->assertEmpty($originalMetadata->getDescription());
        $this->assertEmpty($originalMetadata->getKeywords());

        $updatedFile = $this->xmpWriter->execute(
            $modifiableFilePath,
            $this->metadataFactory->create([
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords
            ])
        );

        $updatedMetadata = $this->xmpReader->execute($updatedFile);

        $this->assertEquals($title, $updatedMetadata->getTitle());
        $this->assertEquals($description, $updatedMetadata->getDescription());
        $this->assertEquals($keywords, $updatedMetadata->getKeywords());
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
                'empty_xmp_image.png',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery',
                ]
            ],
            [
                'itxt_with_empty_xmp_image.png',
                'Title 2',
                'Description 2',
                [
                    'magento2',
                    'mediagallery',
                ]
            ],
        ];
    }
}
