<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Generator;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\Generator\EntityAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\DataObject;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\DefinedClasses;

class EntityAbstractTest extends TestCase
{
    /**#@+
     * Source and result class parameters
     */
    const RESULT_FILE = 'MyResult/MyResult.php';

    const RESULT_DIRECTORY = 'MyResult';

    /**#@-*/

    /**
     * Basic code generation directory
     */
    const GENERATION_DIRECTORY = 'generation';

    /**#@+
     * Generated code before and after code style fix
     */
    const SOURCE_CODE = "a = 1; b = array (); {\n\n some source code \n\n}";

    const RESULT_CODE = "a = 1; b = array(); {\n some generated php code \n}";

    /**#@-*/
    /**
     * Model under test
     *
     * @var EntityAbstract|MockObject
     */
    protected $_model;

    /**
     * @var string
     */
    private $sourceClass;

    /**
     * @var string
     */
    private $resultClass;

    protected function setUp(): void
    {
        $this->sourceClass = '\\' . DataObject::class;
        $this->resultClass = '\\' . \Magento\Framework\DataObject_MyResult::class;
        $this->_model = $this->getMockForAbstractClass(EntityAbstract::class);
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testConstruct()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        // without parameters
        $this->assertAttributeEmpty('_sourceClassName', $this->_model);
        $this->assertAttributeEmpty('_resultClassName', $this->_model);
        $this->assertAttributeInstanceOf(Io::class, '_ioObject', $this->_model);
        $this->assertAttributeInstanceOf(
            ClassGenerator::class,
            '_classGenerator',
            $this->_model
        );
        $this->assertAttributeInstanceOf(
            DefinedClasses::class,
            'definedClasses',
            $this->_model
        );

        // with source class name
        $this->_model = $this->getMockForAbstractClass(
            EntityAbstract::class,
            [$this->sourceClass]
        );
        $this->assertAttributeEquals($this->sourceClass, '_sourceClassName', $this->_model);
        $this->assertAttributeEquals($this->sourceClass . 'Abstract', '_resultClassName', $this->_model);

        // with all arguments
        // Configure IoObject mock
        $ioObject = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();
        $codeGenerator = $this->getMockBuilder(ClassGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = $this->getMockForAbstractClass(
            EntityAbstract::class,
            [$this->sourceClass, $this->resultClass, $ioObject, $codeGenerator]
        );
        $this->assertAttributeEquals($this->resultClass, '_resultClassName', $this->_model);
        $this->assertAttributeEquals($ioObject, '_ioObject', $this->_model);
        $this->assertAttributeEquals($codeGenerator, '_classGenerator', $this->_model);
    }

    /**
     * Data provider for testGenerate method
     *
     * @return array
     */
    public function generateDataProvider()
    {
        return [
            'no_source_class' => [
                'errors' => ['Source class \Magento\Framework\DataObject doesn\'t exist.'],
                'validationSuccess' => false,
                'sourceClassExists' => false,
            ],
            'cant_create_result_directory' => [
                'errors' => ['Can\'t create directory ' . self::RESULT_DIRECTORY . '.'],
                'validationSuccess' => false,
                'sourceClassExists' => true,
                'resultClassExists' => false,
                'makeResultDirSuccess' => false,
            ],
            'result_file_exists' => [
                'errors' => [],
                'validationSuccess' => true,
                'sourceClassExists' => true,
                'resultClassExists' => false,
                'makeResultDirSuccess' => false,
                'resultFileExists' => true,
            ],
            'generate_no_data' => [
                'errors' => ['Can\'t generate source code.'],
                'validationSuccess' => true,
                'sourceClassExists' => true,
                'resultClassExists' => false,
                'makeResultDirSuccess' => true,
                'resultFileExists' => true,
                'willWriteCode' => false,
            ],
            'generate_ok' => []
        ];
    }

    /**
     * @param array $errors
     * @param bool $validationSuccess
     * @param bool $sourceClassExists
     * @param bool $resultClassExists
     * @param bool $makeResultDirSuccess
     * @param bool $resultFileExists
     * @param bool $willWriteCode
     *
     * @dataProvider generateDataProvider
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::generate
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::getErrors
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::getSourceClassName
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_getResultClassName
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_getDefaultResultClassName
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_generateCode
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_addError
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_validateData
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_getClassDocBlock
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_getGeneratedCode
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_fixCodeStyle
     */
    public function testGenerate(
        $errors = [],
        $validationSuccess = true,
        $sourceClassExists = true,
        $resultClassExists = false,
        $makeResultDirSuccess = true,
        $resultFileExists = false,
        $willWriteCode = true
    ) {
        if ($validationSuccess) {
            $arguments = $this->_prepareMocksForGenerateCode($willWriteCode);
        } else {
            $arguments = $this->_prepareMocksForValidateData(
                $sourceClassExists,
                $resultClassExists,
                $makeResultDirSuccess,
                $resultFileExists
            );
        }
        $abstractGetters = ['_getClassProperties', '_getClassMethods'];
        $this->_model = $this->getMockForAbstractClass(
            EntityAbstract::class,
            $arguments,
            '',
            true,
            true,
            true,
            $abstractGetters
        );
        // we need to mock abstract methods to set correct return value type
        foreach ($abstractGetters as $methodName) {
            $this->_model->expects($this->any())->method($methodName)->willReturn([]);
        }

        $result = $this->_model->generate();
        if ($errors) {
            $this->assertFalse($result);
            $this->assertEquals($errors, $this->_model->getErrors());
        } else {
            $this->assertEquals('MyResult/MyResult.php', $result);
            $this->assertEmpty($this->_model->getErrors());
        }
    }

    /**
     * Prepares mocks for validation verification
     *
     * @param bool $sourceClassExists
     * @param bool $resultClassExists
     * @param bool $makeResultDirSuccess
     * @param bool $resultFileExists
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareMocksForValidateData(
        $sourceClassExists = true,
        $resultClassExists = false,
        $makeResultDirSuccess = true,
        $resultFileExists = false
    ) {
        // Configure DefinedClasses mock
        $definedClassesMock = $this->createMock(DefinedClasses::class);
        $definedClassesMock->expects($this->once())
            ->method('isClassLoadable')
            ->with($this->sourceClass)
            ->willReturn($sourceClassExists);
        if ($resultClassExists) {
            $definedClassesMock->expects($this->once())
                ->method('isClassLoadableFromDisk')
                ->with($this->resultClass)
                ->willReturn($resultClassExists);
        }

        // Configure IoObject mock
        $ioObject = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ioObject->expects($this->any())->method('getResultFileDirectory')->willReturn(self::RESULT_DIRECTORY);
        $ioObject->expects($this->any())->method('fileExists')->willReturn($resultFileExists);
        if ($sourceClassExists && !$resultClassExists) {
            $ioObject->expects($this->once())
                ->method('makeResultFileDirectory')
                ->with($this->resultClass)
                ->willReturn($makeResultDirSuccess);
        }

        return [
            'source_class' => $this->sourceClass,
            'result_class' => $this->resultClass,
            'io_object' => $ioObject,
            'code_generator' => null,
            'definedClasses' => $definedClassesMock,
        ];
    }

    /**
     * Prepares mocks for code generation test
     *
     * @param bool $willWriteCode
     * @return array
     */
    protected function _prepareMocksForGenerateCode($willWriteCode)
    {
        // Configure mocks for the validation step
        $mocks = $this->_prepareMocksForValidateData();

        $codeGenerator = $this->getMockBuilder(ClassGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $codeGenerator->expects($this->once())->method('setName')->with($this->resultClass)->willReturnSelf();
        $codeGenerator->expects($this->once())->method('addProperties')->willReturnSelf();
        $codeGenerator->expects($this->once())->method('addMethods')->willReturnSelf();
        $codeGenerator->expects($this->once())
            ->method('setClassDocBlock')
            ->with($this->isType('array'))->willReturnSelf();

        $codeGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($willWriteCode ? self::RESULT_CODE : null);

        // Add configuration for the generation step
        /** @var \PHPUnit\Framework\MockObject\MockObject $ioObject */
        $ioObject = $mocks['io_object'];
        if ($willWriteCode) {
            $ioObject->expects($this->once())->method('writeResultFile')->with(self::RESULT_FILE, self::RESULT_CODE);
        }
        $ioObject->expects($this->any())->method('generateResultFileName')->willReturn(self::RESULT_FILE);

        return [
            'source_class' => $mocks['source_class'],
            'result_class' => $mocks['result_class'],
            'io_object' => $ioObject,
            'code_generator' => $codeGenerator,
            'definedClasses' => $mocks['definedClasses'],
        ];
    }
}
