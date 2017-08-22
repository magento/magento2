<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class ClassNameExtractorTest extends \PHPUnit\Framework\TestCase
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
                'class_with_namespace.txt',
                'Magento\ModuleName\SubDirectoryName\Foo'
            ],
            [
                'class_implements_interface.txt',
                'Magento\ModuleName\SubDirectoryName\Foo'
            ],
            [
                'class_with_comment.txt',
                'Magento\ModuleName\SubDirectoryName\Foo'
            ],
            [
                'missing_class_keyword.txt',
                false
            ],
            [
                'class_without_namespace.txt',
                'Foo'
            ],
            [
            'implements_keyword_on_different_line.txt',
                'Foo'
            ],
            [
                'extra_whitespaces.txt',
                'Foo'
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
                'class_with_namespace.txt',
                'Foo'
            ],
            [
                'missing_class_keyword.txt',
                false
            ],
            [
                'implements_keyword_on_different_line.txt',
                'Foo'
            ],
            [
                'extra_whitespaces.txt',
                'Foo'
            ]
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
                'missing_class_keyword.txt',
                'Magento\ModuleName\SubDirectoryName'
            ],
            [
                'class_without_namespace.txt',
                false
            ]
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
