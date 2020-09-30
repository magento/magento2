<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Test\Integration\Model\Gif\Segment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\MediaGalleryMetadata\Model\Gif\Segment\WriteXmp;
use Magento\MediaGalleryMetadata\Model\Gif\Segment\ReadXmp;
use Magento\MediaGalleryMetadata\Model\Gif\ReadFile;
use Magento\MediaGalleryMetadata\Model\MetadataFactory;

/**
 * Test for XMP reader and writer gif format
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->xmpWriter = Bootstrap::getObjectManager()->get(WriteXmp::class);
        $this->xmpReader = Bootstrap::getObjectManager()->get(ReadXmp::class);
        $this->fileReader = Bootstrap::getObjectManager()->get(ReadFile::class);
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
        $this->metadataFactory = Bootstrap::getObjectManager()->get(MetadataFactory::class);
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
    public function testWriteReadGif(
        string $fileName,
        string $title,
        string $description,
        array $keywords
    ): void {
        $path = realpath(__DIR__ . '/../../../../_files/' . $fileName);
        $file = $this->fileReader->execute($path);
        $originalGifMetadata = $this->xmpReader->execute($file);

        $this->assertEmpty($originalGifMetadata->getTitle());
        $this->assertEmpty($originalGifMetadata->getDescription());
        $this->assertEmpty($originalGifMetadata->getKeywords());
        $updatedGifFile = $this->xmpWriter->execute(
            $file,
            $this->metadataFactory->create([
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords
            ])
        );
        $updatedGifMetadata = $this->xmpReader->execute($updatedGifFile);
        $this->assertEquals($title, $updatedGifMetadata->getTitle());
        $this->assertEquals($description, $updatedGifMetadata->getDescription());
        $this->assertEquals($keywords, $updatedGifMetadata->getKeywords());
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
                'empty_exiftool.gif',
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
