<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Integration\Model\Filesystem;

use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for GetFileInfo
 */
class GetFileInfoTest extends TestCase
{
    /**
     * @var GetFileInfo
     */
    private $getFileInfo;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getFileInfo = Bootstrap::getObjectManager()->get(GetFileInfo::class);
    }

    /**
     * @dataProvider filesProvider
     * @param string $file
     */
    public function testExecute(
        string $file
    ): void {

        $path = $this->getImageFilePath($file);

        $fileInfo = $this->getFileInfo->execute($path);
        $this->assertNotEmpty($fileInfo->getPath());
        $this->assertNotEmpty($fileInfo->getFilename());
        $this->assertNotEmpty($fileInfo->getExtension());
        $this->assertNotEmpty($fileInfo->getBasename());
        $this->assertNotEmpty($fileInfo->getPathname());
        $this->assertNotEmpty($fileInfo->getPerms());
        $this->assertNotEmpty($fileInfo->getInode());
        $this->assertNotEmpty($fileInfo->getSize());
        $this->assertNotEmpty($fileInfo->getOwner());
        $this->assertNotEmpty($fileInfo->getGroup());
        $this->assertNotEmpty($fileInfo->getATime());
        $this->assertNotEmpty($fileInfo->getMTime());
        $this->assertNotEmpty($fileInfo->getCTime());
        $this->assertNotEmpty($fileInfo->getType());
        $this->assertNotEmpty($fileInfo->getRealPath());

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
                'magento.jpg',
                'magento_2.jpg'
            ]
        ];
    }

    /**
     * Return image file path
     *
     * @param string $filename
     * @return string
     */
    private function getImageFilePath(string $filename): string
    {
        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . implode(
                DIRECTORY_SEPARATOR,
                [
                    '_files',
                    $filename
                ]
            );
    }
}
