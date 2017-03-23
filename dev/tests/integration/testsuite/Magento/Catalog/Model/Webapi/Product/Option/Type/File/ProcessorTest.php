<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Webapi\Product\Option\Type\File;

use Magento\Framework\App\Filesystem\DirectoryList;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @dataProvider pathConfigDataProvider
     */
    public function testProcessFileContent($pathConfig)
    {
        $model = $this->getModel($pathConfig);
        /** @var \Magento\Framework\Api\Data\ImageContentInterface $imageContent */
        $imageContent = $this->objectManager->create(
            \Magento\Framework\Api\Data\ImageContentInterface::class
        );
        $imageContent->setName('my_file');
        $imageContent->setType('image/png');
        $imageContent->setBase64EncodedData($this->getImageContent());
        $result = $model->processFileContent($imageContent);

        $this->assertArrayHasKey('fullpath', $result);
        $this->assertTrue(file_exists($result['fullpath']));

        /** @var  $filesystem \Magento\Framework\Filesystem */
        $filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->assertArrayHasKey('quote_path', $result);
        $filePath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($result['quote_path']);
        $this->assertTrue(file_exists($filePath));

        $this->assertArrayHasKey('order_path', $result);
        $filePath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($result['order_path']);
        $this->assertTrue(file_exists($filePath));
    }

    public function pathConfigDataProvider()
    {
        return [
            // default config
            [[]],
            // config from pub/index.php
            [
                [
                    DirectoryList::PUB => [DirectoryList::URL_PATH => ''],
                    DirectoryList::MEDIA => [DirectoryList::URL_PATH => 'media'],
                    DirectoryList::STATIC_VIEW => [DirectoryList::URL_PATH => 'static'],
                    DirectoryList::UPLOAD => [DirectoryList::URL_PATH => 'media/upload'],
                ]
            ],
        ];
    }

    /**
     * @return \Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor
     */
    private function getModel($pathConfig)
    {
        $rootPath = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppTempDir();
        $dirList = $this->objectManager->create(
            \Magento\Framework\App\Filesystem\DirectoryList::class,
            ['root' => $rootPath, 'config' => $pathConfig]
        );
        $fileSystem = $this->objectManager->create(
            \Magento\Framework\Filesystem::class,
            ['directoryList' => $dirList]
        );
        $model = $this->objectManager->create(
            \Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor::class,
            ['filesystem' => $fileSystem]
        );
        return $model;
    }

    /**
     * Black rectangle 10x10px
     *
     * @return string
     */
    private function getImageContent()
    {
        return 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKEAIAAABSwISpAAAACXBIWXMAAABIAAAASABGyWs+AAAACXZwQWcAAAA' .
        'KAAAACgBOpnblAAAAD0lEQVQoz2NgGAWjgJYAAAJiAAEQ3MCgAAAAJXRFWHRjcmVhdGUtZGF0ZQAyMDA5LTA3LTA4VDE5Oj' .
        'E1OjMyKzAyOjAwm1PZQQAAACV0RVh0bW9kaWZ5LWRhdGUAMjAwOS0wNy0wOFQxOToxNTozMiswMjowMMTir3UAAAAASUVORK5CYII=';
    }
}
