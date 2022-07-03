<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Test\Integration\Model\Jpeg\Segment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\MediaGalleryMetadata\Model\Jpeg\Segment\WriteXmp;
use Magento\MediaGalleryMetadata\Model\Jpeg\Segment\ReadXmp;
use Magento\MediaGalleryMetadata\Model\Jpeg\ReadFile;
use Magento\MediaGalleryMetadata\Model\MetadataFactory;

/**
 * Test for XMP reader and writer
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
    private $directory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->xmpWriter = Bootstrap::getObjectManager()->get(WriteXmp::class);
        $this->xmpReader = Bootstrap::getObjectManager()->get(ReadXmp::class);
        $this->fileReader = Bootstrap::getObjectManager()->get(ReadFile::class);
        $this->directory = Bootstrap::getObjectManager()->get(FileSystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->metadataFactory = Bootstrap::getObjectManager()->get(MetadataFactory::class);
        $this->directory->create('testDir');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->directory->delete('testDir');
    }

    /**
     * Test for XMP reader and writer
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
        $path = $this->directory->getAbsolutePath('testDir/' . $fileName);
        $this->directory->getDriver()->filePutContents(
            $path,
            file_get_contents(__DIR__ . '/../../../../_files/' . $fileName)
        );
        $file = $this->fileReader->execute($path);
        $originalMetadata = $this->xmpReader->execute($file);

        $this->assertEmpty($originalMetadata->getTitle());
        $this->assertEmpty($originalMetadata->getDescription());
        $this->assertEmpty($originalMetadata->getKeywords());
        $updatedFile = $this->xmpWriter->execute(
            $file,
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
                'empty_xmp_image.jpeg',
                'Title of the magento image',
                'Description of the magento image 2',
                [
                    'magento2',
                    'community'
                ]
            ]
        ];
    }
}
