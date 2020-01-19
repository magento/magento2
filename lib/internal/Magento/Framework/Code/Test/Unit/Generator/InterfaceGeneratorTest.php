<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Generator;

class InterfaceGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Code\Generator\InterfaceGenerator
     */
    protected $interfaceGenerator;

    /**
     * Doc block test data
     *
     * @var array
     */
    protected $interfaceDocBlock = [
        'shortDescription' => 'Interface short description.',
        'longDescription' => "Interface long\ndescription.",
        'tags' => [
            'tag1' => ['name' => 'tag1', 'description' => 'data1'],
            'tag2' => ['name' => 'tag2', 'description' => 'data2'],
        ],
    ];

    /**
     * Method test data
     *
     * @var array
     */
    protected $methodsData = [
        'testMethod1' => [
            'name' => 'testMethod1',
            'static' => true,
            'parameters' => [
                ['name' => 'data', 'type' => 'array', 'defaultValue' => [], 'passedByReference' => true],
            ],
            'docblock' => [
                'shortDescription' => 'Method short description',
                'tags' => [
                    ['name' => 'param', 'description' => 'array $data'],
                    ['name' => 'return', 'description' => 'TestThree'],
                ],
            ],
        ],
        'testMethod2' => [
            'name' => 'testMethod2',
            'parameters' => [
                ['name' => 'data', 'defaultValue' => 'test_default'],
                ['name' => 'flag', 'defaultValue' => true],
            ],
            'docblock' => [
                'shortDescription' => 'Method short description',
                'longDescription' => "Method long\ndescription",
                'tags' => [
                    ['name' => 'param', 'description' => 'string $data'],
                    ['name' => 'param', 'description' => 'bool $flag'],
                ],
            ],
        ],
        'testMethod3' => ['name' => 'testMethod3'],
    ];

    protected function setUp()
    {
        $this->interfaceGenerator = new \Magento\Framework\Code\Generator\InterfaceGenerator();
    }

    /**
     * @dataProvider generateDataProvider
     */
    public function testGenerate($additionalMethodsData, $expectedException, $expectedExceptionMessage)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }
        $methodsData = array_merge_recursive($this->methodsData, $additionalMethodsData);
        $this->interfaceGenerator->setClassDocBlock($this->interfaceDocBlock)
            ->addMethods($methodsData)
            ->setName('SevenInterface')
            ->setNamespaceName(\Magento\SomeModule\Model::class)
            ->addUse(\Magento\SomeModule\Model\Two\Test::class, 'TestTwo')
            ->addUse(\Magento\SomeModule\Model\Three\Test::class, 'TestThree')
            ->setExtendedClass(\Magento\Framework\Code\Generator\CodeGeneratorInterface::class);
        $generatedInterface = $this->interfaceGenerator->generate();
        $expectedInterface = file_get_contents(
            __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/SevenInterface.php'
        );

        $this->assertStringEndsWith(
            $generatedInterface,
            $expectedInterface,
            "Interface was generated incorrectly."
        );
    }

    public function testGeneratePredefinedContent()
    {
        $expectedContent = 'Expected generated content.';
        $this->interfaceGenerator->setSourceDirty(false)->setSourceContent($expectedContent);
        $generatedContent = $this->interfaceGenerator->generate();
        $this->assertEquals($expectedContent, $generatedContent, "Generated content is invalid.");
    }

    public function testGeneratePredefinedContentNotSet()
    {
        $expectedContent = '';
        $this->interfaceGenerator->setSourceDirty(false);
        $generatedContent = $this->interfaceGenerator->generate();
        $this->assertEquals($expectedContent, $generatedContent, "Generated content is invalid.");
    }

    /**
     * @return array
     */
    public function generateDataProvider()
    {
        return [
            'Valid data' => [
                'additionalMethodsData' => [],
                'expectedException' => '',
                'expectedExceptionMessage' => ''
            ],
            '"final" usage exception' => [
                'additionalMethodsData' => ['testMethod1' => ['final' => true]],
                'expectedException' => '\LogicException',
                'expectedExceptionMessage' => "Interface method cannot be marked as 'final'. Method name: 'testMethod1'"
            ],
            'Non public interface method  exception' => [
                'additionalMethodsData' => ['testMethod2' => ['visibility' => 'protected']],
                'expectedException' => '\LogicException',
                'expectedExceptionMessage' =>
                    "Interface method visibility can only be 'public'. Method name: 'testMethod2'"
            ],
            '"abstract" usage exception' => [
                'additionalMethodsData' => ['testMethod1' => ['abstract' => true]],
                'expectedException' => '\LogicException',
                'expectedExceptionMessage' =>
                    "'abstract' modifier cannot be used for interface method. Method name: 'testMethod1'"
            ],
        ];
    }
}
