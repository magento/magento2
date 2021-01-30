<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Test\Integration\Model\Jpeg\Segment;

use Magento\Framework\Exception\LocalizedException;
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
    public function testWriteRead(
        string $fileName,
        string $title,
        string $description,
        array $keywords
    ): void {
        $path = realpath(__DIR__ . '/../../../../_files/' . $fileName);
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
