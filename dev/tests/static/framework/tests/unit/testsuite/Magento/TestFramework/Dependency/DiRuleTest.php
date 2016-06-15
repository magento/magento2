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
        $file = '/some/path/di.xml';
        $this->assertEquals(
            $expected,
            $this->model->getDependencyInfo($module, 'any', $file, $contents)
        );
    }

    /**
     * @return array
     */
    public function getDependencyInfoDataProvider()
    {
        return [
            'Di without dependencies' => [
                'Magento\SomeModule',
                $this->getFileContent('di_no_dependency.xml'),
                []
            ],
            'Di only in module dependencies' => [
                'Magento\SomeModule',
                $this->getFileContent('di_in_module_dependency.xml'),
                []
            ],
            'Di external dependencies' => [
                'Magento\SomeModule',
                $this->getFileContent('di_external_dependency.xml'),
                [
                    [
                        'module' => 'Magento\ExternalModule3',
                        'type' => RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\ExternalModule3\Some\Another\Class'
                    ],
                    [
                        'module' => 'Magento\ExternalModule5',
                        'type' => RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\ExternalModule5\Some\Another\Class'
                    ],
                    [
                        'module' => 'Magento\ExternalModule6',
                        'type' => RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\ExternalModule6\Some\Plugin\Class'
                    ],
                    [
                        'module' => 'Magento\ExternalModule4',
                        'type' => RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\ExternalModule4\Some\Argument4',
                    ],
                    [
                        'module' => 'Magento\ExternalModule1',
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento\ExternalModule1\Some\Argument1'
                    ],
                    [
                        'module' => 'Magento\ExternalModule2',
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento\ExternalModule2\Some\Argument2'
                    ],
                    [
                        'module' => 'Magento\ExternalModule4',
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento\ExternalModule4\Some\Argument3'
                    ]
                ]
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
