<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class ClassNameExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @param string $className
     * @dataProvider getNameWithNamespaceDataProvider
     */
    public function testGetNameWithNamespace($file, $className)
    {
        $classNameExtractor = new \Magento\TestFramework\Utility\ClassNameExtractor();
        $this->assertEquals(
            $classNameExtractor->getNameWithNamespace($this->getFileContent($file)),
            $className
        );

    }

    /**
     * @return array
     */
    public function getNameWithNamespaceDataProvider()
    {
        return [
            [
                'class_foo1.txt',
                'Magento\ModuleName\SubDirectoryName\Foo'
            ],
            [
                'class_foo2.txt',
                'Magento\ModuleName\SubDirectoryName\Foo'
            ],
            [
                'class_foo3.txt',
                'Magento\ModuleName\SubDirectoryName\Foo'
            ],
            [
                'class_foo4.txt',
                false
            ],
            [
                'class_foo5.txt',
                false
            ]
        ];
    }

    /**
     * @param string $file
     * @param string $className
     * @dataProvider getNameDataProvider
     */
    public function testGetName($file, $className)
    {
        $classNameExtractor = new \Magento\TestFramework\Utility\ClassNameExtractor();
        $this->assertEquals(
            $classNameExtractor->getName($this->getFileContent($file)),
            $className
        );

    }

    /**
     * @return array
     */
    public function getNameDataProvider()
    {
        return [
            [
                'class_foo1.txt',
                'Foo'
            ],
            [
                'class_foo4.txt',
                false
            ],
        ];
    }

    /**
     * @param string $file
     * @param string $className
     * @dataProvider getNamespaceDataProvider
     */
    public function testGetNamespace($file, $className)
    {
        $classNameExtractor = new \Magento\TestFramework\Utility\ClassNameExtractor();
        $this->assertEquals(
            $classNameExtractor->getNamespace($this->getFileContent($file)),
            $className
        );

    }

    /**
     * @return array
     */
    public function getNamespaceDataProvider()
    {
        return [
            [
                'class_foo4.txt',
                'Magento\ModuleName\SubDirectoryName'
            ],
            [
                'class_foo5.txt',
                false
            ],
        ];
    }

    /**
     * @param $file
     * @return bool|string
     */
    private function getFileContent($file)
    {
        return file_get_contents(__DIR__ . '/_files/' . $file);
    }
}
