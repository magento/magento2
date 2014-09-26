<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Modular;

class IndexerConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configuration acl file list
     *
     * @var array
     */
    protected $fileList = array();

    /**
     * Path to scheme file
     *
     * @var string
     */
    protected $schemeFile;

    protected function setUp()
    {
        $this->schemeFile = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Filesystem'
        )->getPath(
            \Magento\Framework\App\Filesystem::APP_DIR
        ) . '/code/Magento/Indexer/etc/indexer.xsd';
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
        $fileList = glob(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\Filesystem'
            )->getPath(
                \Magento\Framework\App\Filesystem::APP_DIR
            ) . '/*/*/*/etc/indexer.xml'
        );
        $dataProviderResult = array();
        foreach ($fileList as $file) {
            $dataProviderResult[$file] = array($file);
        }
        return $dataProviderResult;
    }
}
