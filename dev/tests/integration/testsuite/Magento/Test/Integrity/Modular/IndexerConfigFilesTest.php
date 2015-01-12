<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class IndexerConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configuration acl file list
     *
     * @var array
     */
    protected $fileList = [];

    /**
     * Path to scheme file
     *
     * @var string
     */
    protected $schemeFile;

    protected function setUp()
    {
        /** @var Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        );
        $this->schemeFile = $filesystem->getDirectoryRead(DirectoryList::APP)
            ->getAbsolutePath('code/Magento/Indexer/etc/indexer.xsd');
    }

    /**
     * Test each acl configuration file
     * @param string $file
     * @dataProvider indexerConfigFileDataProvider
     */
    public function testIndexerConfigFile($file)
    {
        $domConfig = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $result = $domConfig->validate($this->schemeFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * @return array
     */
    public function indexerConfigFileDataProvider()
    {
        /** @var Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        );
        $fileList = glob(
            $filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath() . '/*/*/*/etc/indexer.xml'
        );
        $dataProviderResult = [];
        foreach ($fileList as $file) {
            $dataProviderResult[$file] = [$file];
        }
        return $dataProviderResult;
    }
}
