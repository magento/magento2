<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

class EntityAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Source and result class parameters
     */
    const SOURCE_CLASS = 'Magento\Framework\Object';

    const RESULT_CLASS = 'Magento\Framework\Object_MyResult';

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

    const RESULT_CODE = "a = 1; b = array(); {\n some source code \n}";

    /**#@-*/

    /**
     * Model under test
     *
     * @var \Magento\Framework\Code\Generator\EntityAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Magento\Framework\Code\Generator\EntityAbstract');
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testConstruct()
    {
        // without parameters
        $this->assertAttributeEmpty('_sourceClassName', $this->_model);
        $this->assertAttributeEmpty('_resultClassName', $this->_model);
        $this->assertAttributeInstanceOf('Magento\Framework\Code\Generator\Io', '_ioObject', $this->_model);
        $this->assertAttributeInstanceOf(
            'Magento\Framework\Code\Generator\CodeGenerator\Zend',
            '_classGenerator',
            $this->_model
        );
        $this->assertAttributeInstanceOf(
            'Magento\Framework\Code\Generator\DefinedClasses',
            'definedClasses',
            $this->_model
        );

        // with source class name
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Code\Generator\EntityAbstract',
            [self::SOURCE_CLASS]
        );
        $this->assertAttributeEquals(self::SOURCE_CLASS, '_sourceClassName', $this->_model);
        $this->assertAttributeEquals(self::SOURCE_CLASS . 'Abstract', '_resultClassName', $this->_model);

        // with all arguments
        $ioObject = $this->getMock('Magento\Framework\Code\Generator\Io', [], [], '', false);
        $codeGenerator = $this->getMock(
            'Magento\Framework\Code\Generator\CodeGenerator\Zend',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Code\Generator\EntityAbstract',
            [self::SOURCE_CLASS, self::RESULT_CLASS, $ioObject, $codeGenerator]
        );
        $this->assertAttributeEquals(self::RESULT_CLASS, '_resultClassName', $this->_model);
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
                '$errors' => ['Source class ' . self::SOURCE_CLASS . ' doesn\'t exist.'],
                '$isGeneration' => false,
                '$classExistsFirst' => false,
            ],
            'result_class_exists' => [
                '$errors' => ['Result class ' . self::RESULT_CLASS . ' already exists.'],
                '$isGeneration' => false,
                '$classExistsFirst' => true,
                '$classExistsSecond' => true,
            ],
            'cant_create_generation_directory' => [
                '$errors' => ['Can\'t create directory ' . self::GENERATION_DIRECTORY . '.'],
                '$isGeneration' => false,
                '$classExistsFirst' => true,
                '$classExistsSecond' => false,
                '$makeGeneration' => false,
            ],
            'cant_create_result_directory' => [
                '$errors' => ['Can\'t create directory ' . self::RESULT_DIRECTORY . '.'],
                '$isGeneration' => false,
                '$classExistsFirst' => true,
                '$classExistsSecond' => false,
                '$makeGeneration' => true,
                '$makeResultFile' => false,
            ],
            'result_file_exists' => [
                '$errors' => ['Result file ' . self::RESULT_FILE . ' already exists.'],
                '$isGeneration' => false,
                '$classExistsFirst' => true,
                '$classExistsSecond' => false,
                '$makeGeneration' => true,
                '$makeResultFile' => true,
                '$fileExists' => true,
            ],
            'generate_no_data' => [
                '$errors' => ['Can\'t generate source code.'],
                '$isGeneration' => true,
                '$classExistsFirst' => true,
                '$classExistsSecond' => false,
                '$makeGeneration' => true,
                '$makeResultFile' => true,
                '$fileExists' => true,
                '$isValid' => false,
            ],
            'generate_ok' => []
        ];
    }

    /**
     * @param array $errors
     * @param bool $isGeneration
     * @param bool $classExistsFirst
     * @param bool $classExistsSecond
     * @param bool $makeGeneration
     * @param bool $makeResultFile
     * @param bool $fileExists
     * @param bool $isValid
     *
     * @dataProvider generateDataProvider
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::generate
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::getErrors
     * @covers \Magento\Framework\Code\Generator\EntityAbstract::_getSourceClassName
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
        $isGeneration = true,
        $classExistsFirst = true,
        $classExistsSecond = false,
        $makeGeneration = true,
        $makeResultFile = true,
        $fileExists = false,
        $isValid = true
    ) {
        if ($isGeneration) {
            $arguments = $this->_prepareMocksForGenerateCode($isValid);
        } else {
            $arguments = $this->_prepareMocksForValidateData(
                $classExistsFirst,
                $classExistsSecond,
                $makeGeneration,
                $makeResultFile,
                $fileExists
            );
        }
        $abstractGetters = ['_getClassProperties', '_getClassMethods'];
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Code\Generator\EntityAbstract',
            $arguments,
            '',
            true,
            true,
            true,
            $abstractGetters
        );
        // we need to mock abstract methods to set correct return value type
        foreach ($abstractGetters as $methodName) {
            $this->_model->expects($this->any())->method($methodName)->will($this->returnValue([]));
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
     * @param bool $classExistsFirst
     * @param bool $classExistsSecond
     * @param bool $makeGeneration
     * @param bool $makeResultFile
     * @param bool $fileExists
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareMocksForValidateData(
        $classExistsFirst = true,
        $classExistsSecond = false,
        $makeGeneration = true,
        $makeResultFile = true,
        $fileExists = false
    ) {
        $ioObject = $this->getMock(
            'Magento\Framework\Code\Generator\Io',
            [
                'getResultFileName',
                'makeGenerationDirectory',
                'makeResultFileDirectory',
                'fileExists',
                'getGenerationDirectory',
                'getResultFileDirectory',
                'writeResultFile'
            ],
            [],
            '',
            false
        );
        $definedClassesMock = $this->getMock('Magento\Framework\Code\Generator\DefinedClasses');
        $ioObject->expects(
            $this->any()
        )->method(
            'getResultFileName'
        )->with(
            self::RESULT_CLASS
        )->will(
            $this->returnValue(self::RESULT_FILE)
        );
        $ioObject->expects(
            $this->any()
        )->method(
            'getGenerationDirectory'
        )->will(
            $this->returnValue(self::GENERATION_DIRECTORY)
        );
        $ioObject->expects(
            $this->any()
        )->method(
            'getResultFileDirectory'
        )->will(
            $this->returnValue(self::RESULT_DIRECTORY)
        );

        $definedClassesMock->expects(
            $this->at(0)
        )->method(
            'classLoadable'
        )->with(
            self::SOURCE_CLASS
        )->will(
            $this->returnValue($classExistsFirst)
        );
        if ($classExistsSecond) {
            $definedClassesMock->expects(
                $this->at(1)
            )->method(
                'classLoadable'
            )->with(
                self::RESULT_CLASS
            )->will(
                $this->returnValue($classExistsSecond)
            );
        }

        $expectedInvocations = 1;
        if (!$classExistsFirst || $classExistsSecond) {
            $expectedInvocations = 0;
        }
        $ioObject->expects(
            $this->exactly($expectedInvocations)
        )->method(
            'makeGenerationDirectory'
        )->will(
            $this->returnValue($makeGeneration)
        );

        $this->_prepareIoObjectExpectations(
            $ioObject,
            $classExistsFirst,
            $classExistsSecond,
            $makeGeneration,
            $makeResultFile,
            $fileExists
        );

        return [
            'source_class' => self::SOURCE_CLASS,
            'result_class' => self::RESULT_CLASS,
            'io_object' => $ioObject,
            'code_generator' => null,
            'definedClasses' => $definedClassesMock,
        ];
    }

    /**
     * @param $ioObject \PHPUnit_Framework_MockObject_MockObject
     * @param bool $classExistsFirst
     * @param bool $classExistsSecond
     * @param bool $makeGeneration
     * @param bool $makeResultFile
     * @param bool $fileExists
     */
    protected function _prepareIoObjectExpectations(
        $ioObject,
        $classExistsFirst,
        $classExistsSecond,
        $makeGeneration,
        $makeResultFile,
        $fileExists
    ) {
        if ($classExistsFirst && !$classExistsSecond && $makeGeneration) {
            $ioObject->expects(
                $this->once()
            )->method(
                'makeResultFileDirectory'
            )->with(
                self::RESULT_CLASS
            )->will(
                $this->returnValue($makeResultFile)
            );
        }

        if ($classExistsFirst && !$classExistsSecond && $makeGeneration && $makeResultFile) {
            $ioObject->expects(
                $this->once()
            )->method(
                'fileExists'
            )->with(
                self::RESULT_FILE
            )->will(
                $this->returnValue($fileExists)
            );
        }
    }

    /**
     * Prepares mocks for code generation test
     *
     * @param bool $isValid
     * @return array
     */
    protected function _prepareMocksForGenerateCode($isValid)
    {
        $mocks = $this->_prepareMocksForValidateData();

        $codeGenerator = $this->getMock(
            'Magento\Framework\Code\Generator\CodeGenerator\Zend',
            ['setName', 'addProperties', 'addMethods', 'setClassDocBlock', 'generate'],
            [],
            '',
            false
        );
        $codeGenerator->expects($this->once())->method('setName')->with(self::RESULT_CLASS)->will($this->returnSelf());
        $codeGenerator->expects($this->once())->method('addProperties')->will($this->returnSelf());
        $codeGenerator->expects($this->once())->method('addMethods')->will($this->returnSelf());
        $codeGenerator->expects(
            $this->once()
        )->method(
            'setClassDocBlock'
        )->with(
            $this->isType('array')
        )->will(
            $this->returnSelf()
        );

        $codeGenerator->expects(
            $this->once()
        )->method(
            'generate'
        )->will(
            $this->returnValue($isValid ? self::SOURCE_CODE : null)
        );

        /** @var $ioObject \PHPUnit_Framework_MockObject_MockObject */
        $ioObject = $mocks['io_object'];
        if ($isValid) {
            $ioObject->expects($this->once())->method('writeResultFile')->with(self::RESULT_FILE, self::RESULT_CODE);
        }

        return [
            'source_class' => $mocks['source_class'],
            'result_class' => $mocks['result_class'],
            'io_object' => $ioObject,
            'code_generator' => $codeGenerator,
            'definedClasses' => $mocks['definedClasses'],
        ];
    }
}
