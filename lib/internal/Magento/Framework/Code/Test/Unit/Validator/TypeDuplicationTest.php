<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Validator;

require_once '_files/ClassesForTypeDuplication.php';
class TypeDuplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Code\Validator\TypeDuplication
     */
    protected $_validator;

    /**
     * @var string
     */
    protected $_fixturePath;

    protected function setUp()
    {
        $path = realpath(__DIR__) . '/' . '_files' . '/' . 'ClassesForTypeDuplication.php';
        $this->_fixturePath = str_replace('\\', '/', $path);
        $this->_validator = new \Magento\Framework\Code\Validator\TypeDuplication();
    }

    /**
     * @param $className
     * @dataProvider validClassesDataProvider
     */
    public function testValidClasses($className)
    {
        $this->assertTrue($this->_validator->validate($className));
    }

    public function validClassesDataProvider()
    {
        return [
            'Duplicated interface injection' => ['\TypeDuplication\ValidClassWithTheSameInterfaceTypeArguments'],
            'Class with sub type arguments' => ['\TypeDuplication\ValidClassWithSubTypeArguments'],
            'Class with SuppressWarnings' => ['\TypeDuplication\ValidClassWithSuppressWarnings']
        ];
    }

    public function testInvalidClass()
    {
        $message = 'Argument type duplication in class TypeDuplication\InvalidClassWithDuplicatedTypes in ' .
            $this->_fixturePath .
            PHP_EOL .
            'Multiple type injection [\TypeDuplication\ArgumentBaseClass]';
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class, $message);
        $this->_validator->validate('\TypeDuplication\InvalidClassWithDuplicatedTypes');
    }
}
