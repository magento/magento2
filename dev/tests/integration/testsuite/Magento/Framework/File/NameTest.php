<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Framework\File\Uploader
 *
 * @magentoDataFixture Magento/Framework/File/_files/framework_file_name.php
 */
class NameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Name
     */
    private $nameModel;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->nameModel = $objectManager->get(Name::class);
    }

    /**
     *
     * @return void
     * @magentoDataFixture Magento/Framework/File/_files/framework_file_name.php
     *
     * @dataProvider getNewFileNameDataProvider
     */

    /**
     * @param string $destinationFile
     * @param string $expectedFileName
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     *
     * @dataProvider getNewFileNameDataProvider
     */
    public function testGetNewFileName($destinationFile, $expectedFileName)
    {
        $path = $this->mediaDirectory->getAbsolutePath('image/' . $destinationFile);
        $name = $this->nameModel->getNewFileName($path);
        $this->assertEquals($expectedFileName, $name);
    }

    public function getNewFileNameDataProvider()
    {
        return [
            ['image.jpg', 'image.jpg'],
            ['image_one.jpg', 'image_one_1.jpg'],
            ['image_two.jpg', 'image_two_2.jpg']
        ];
    }
}
