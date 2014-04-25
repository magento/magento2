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
namespace Magento\Framework\Module\Declaration;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for get method
     *
     * @dataProvider providerGet
     * @param $baseDir
     * @param $file
     * @param $scope
     * @param $expectedFileList
     */
    public function testGet($baseDir, $file, $scope, $expectedFileList)
    {
        $fileResolver = $this->getFileResolver($baseDir);

        $fileIterator = $fileResolver->get($file, $scope);
        $fileList = array();
        foreach ($fileIterator as $filePath) {
            $fileList[] = $filePath;
        }
        $this->assertEquals(sort($fileList), sort($expectedFileList));
    }

    /**
     * Data provider for testGet
     *
     * @return array
     */
    public function providerGet()
    {
        return array(
            array(
                __DIR__ . '/FileResolver/_files',
                'module.xml',
                'global',
                array(
                    file_get_contents(__DIR__ . '/FileResolver/_files/app/code/Module/Four/etc/module.xml'),
                    file_get_contents(__DIR__ . '/FileResolver/_files/app/code/Module/One/etc/module.xml'),
                    file_get_contents(__DIR__ . '/FileResolver/_files/app/code/Module/Three/etc/module.xml'),
                    file_get_contents(__DIR__ . '/FileResolver/_files/app/code/Module/Two/etc/module.xml'),
                    file_get_contents(__DIR__ . '/FileResolver/_files/app/etc/custom/module.xml')
                )
            )
        );
    }

    /**
     * Get file resolver instance
     *
     * @param string $baseDir
     * @return FileResolver
     */
    protected function getFileResolver($baseDir)
    {
        $filesystem = new \Magento\Framework\App\Filesystem(
            new \Magento\Framework\App\Filesystem\DirectoryList($baseDir),
            new \Magento\Framework\Filesystem\Directory\ReadFactory(),
            new \Magento\Framework\Filesystem\Directory\WriteFactory()
        );
        $iteratorFactory = new \Magento\Framework\Config\FileIteratorFactory();

        return new \Magento\Framework\Module\Declaration\FileResolver($filesystem, $iteratorFactory);
    }
}
