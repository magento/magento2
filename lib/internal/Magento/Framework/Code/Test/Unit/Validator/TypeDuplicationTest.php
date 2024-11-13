<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\Validator\TypeDuplication;
use Magento\Framework\Exception\ValidatorException;

require_once '_files/ClassesForTypeDuplication.php';
class TypeDuplicationTest extends TestCase
{
    /**
     * @var \Magento\Framework\Code\Validator\TypeDuplication
     */
    protected $_validator;

    /**
     * @var string
     */
    protected $_fixturePath;

    protected function setUp(): void
    {
        $path = realpath(__DIR__) . '/' . '_files' . '/' . 'ClassesForTypeDuplication.php';
        $this->_fixturePath = str_replace('\\', '/', $path);
        $this->_validator = new TypeDuplication();
    }

    /**
     * @param $className
     * @dataProvider validClassesDataProvider
     */
    public function testValidClasses($className)
    {
        $this->assertTrue($this->_validator->validate($className));
    }

    /**
     * @return array
     */
    public static function validClassesDataProvider()
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
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage($message);
        $this->_validator->validate('\TypeDuplication\InvalidClassWithDuplicatedTypes');
    }
}
