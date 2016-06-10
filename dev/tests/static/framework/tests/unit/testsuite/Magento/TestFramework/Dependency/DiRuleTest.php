<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

class DiRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiRule
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new DiRule();
    }
    /**
     * @param string $module
     * @param string $contents
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($module, $contents, array $expected)
    {
        $file = '/some/path/not_di.xml';
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'any', $file, $contents));
    }

    public function getDependencyInfoDataProvider()
    {
        return [
            'Di without dependencies' => [
                'Magento\SomeModule',
                $this->getFileContent('di_no_dependency.xml'),
                []
            ]
        ];
    }

    /**
     * Get content of di file
     *
     * @param string $fileName
     * @return string
     */
    private function getFileContent($fileName)
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $fileName);
    }
}
