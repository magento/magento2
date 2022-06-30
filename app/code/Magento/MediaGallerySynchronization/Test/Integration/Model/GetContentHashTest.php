<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Integration\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGallerySynchronization\Model\GetContentHash;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for GetContentHash.
 */
class GetContentHashTest extends TestCase
{
    /**
     * @var GetContentHash
     */
    private $getContentHash;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getContentHash = Bootstrap::getObjectManager()->get(GetContentHash::class);
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
    }

    /**
     * Test for GetContentHash::execute
     *
     * @dataProvider filesProvider
     * @param string $firstFile
     * @param string $secondFile
     * @param bool $isEqual
     * @throws FileSystemException
     */
    public function testExecute(
        string $firstFile,
        string $secondFile,
        bool $isEqual
    ): void {
        $firstHash = $this->getContentHash->execute($this->getImageContent($firstFile));
        $secondHash = $this->getContentHash->execute($this->getImageContent($secondFile));
        $isEqual ? $this->assertEquals($firstHash, $secondHash) : $this->assertNotEquals($firstHash, $secondHash);
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
                'magento_2.jpg',
                true
            ],
            [
                'magento.jpg',
                'magento_3.png',
                false
            ]
        ];
    }

    /**
     * Get image file content.
     *
     * @param string $filename
     * @return string
     * @throws FileSystemException
     */
    private function getImageContent(string $filename): string
    {
        return $this->driver->fileGetContents($this->getImageFilePath($filename));
    }

    /**
     * Return image file path
     *
     * @param string $filename
     * @return string
     */
    private function getImageFilePath(string $filename): string
    {
        return dirname(__DIR__, 1)
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
