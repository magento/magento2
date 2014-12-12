<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\App\Filesystem\DirectoryList;

class MviewConfigFilesTest extends \PHPUnit_Framework_TestCase
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
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        );
        $this->schemeFile = $filesystem->getDirectoryRead(DirectoryList::LIB_INTERNAL)
            ->getAbsolutePath('Magento/Framework/Mview/etc/mview.xsd');
    }

    /**
     * Test each acl configuration file
     * @param string $file
     * @dataProvider mviewConfigFileDataProvider
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
    public function mviewConfigFileDataProvider()
    {
        return \Magento\Framework\Test\Utility\Files::init()->getConfigFiles('mview.xml');
    }
}
