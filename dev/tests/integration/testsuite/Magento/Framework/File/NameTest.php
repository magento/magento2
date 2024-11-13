<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem;

/**
 * Test for \Magento\Framework\File\Name
 */
class NameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Name
     */
    private $nameModel;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $this->nameModel = $objectManager->get(Name::class);
    }

    /**
     * @param string $destinationFile
     * @param string $expectedFileName
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     *
     * @magentoDataFixture Magento/Framework/File/_files/framework_file_name.php
     * @dataProvider getNewFileNameDataProvider
     */
    public function testGetNewFileName($directory, $destinationFile, $expectedFileName)
    {
        $directory = $this->fileSystem->getDirectoryWrite($directory);
        $path = $directory->getAbsolutePath('image/' . $destinationFile);
        $name = $this->nameModel->getNewFileName($path);
        $this->assertEquals($expectedFileName, $name);
    }

    /**
     * Data provider for testGetNewFileName
     * @return array
     */
    public static function getNewFileNameDataProvider()
    {
        return [
            [DirectoryList::VAR_DIR, 'image.jpg', 'image.jpg'],
            [DirectoryList::VAR_DIR, 'image_one.jpg', 'image_one_1.jpg'],
            [DirectoryList::MEDIA, 'image.jpg', 'image.jpg'],
            [DirectoryList::MEDIA, 'image_one.jpg', 'image_one_1.jpg'],
            [DirectoryList::MEDIA, 'image_two.jpg', 'image_two_2.jpg']
        ];
    }
}
