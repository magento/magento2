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
use Magento\MediaGalleryMetadata\Model\Jpeg\Segment\WriteIptc;
use Magento\MediaGalleryMetadata\Model\Jpeg\Segment\ReadIptc;
use Magento\MediaGalleryMetadata\Model\Jpeg\ReadFile;
use Magento\MediaGalleryMetadata\Model\MetadataFactory;

/**
 * Test for IPTC reader and writer
 */
class IptcTest extends TestCase
{
    /**
     * @var WriteIptc
     */
    private $iptcWriter;

    /**
     * @var ReadIptc
     */
    private $iptcReader;

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
        $this->directory = Bootstrap::getObjectManager()->get(FileSystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->iptcWriter = Bootstrap::getObjectManager()->get(WriteIptc::class);
        $this->iptcReader = Bootstrap::getObjectManager()->get(ReadIptc::class);
        $this->fileReader = Bootstrap::getObjectManager()->get(ReadFile::class);
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
     * Test for IPTC reader and writer
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
        $modifiableFilePath = $this->fileReader->execute($path);
        $originalMetadata = $this->iptcReader->execute($modifiableFilePath);

        $this->assertEmpty($originalMetadata->getTitle());
        $this->assertEmpty($originalMetadata->getDescription());
        $this->assertEmpty($originalMetadata->getKeywords());

        $updatedFile = $this->iptcWriter->execute(
            $modifiableFilePath,
            $this->metadataFactory->create([
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords
            ])
        );

        $updatedMetadata = $this->iptcReader->execute($updatedFile);

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
                'empty_iptc.jpeg',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery'
                ]
            ]
        ];
    }
}
