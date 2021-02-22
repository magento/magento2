<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Validator;

require_once '_files/ClassesForArgumentSequence.php';
class ArgumentSequenceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Code\Validator\ArgumentSequence
     */
    protected $_validator;

    /**
     * @var string
     */
    protected $_fixturePath;

    protected function setUp(): void
    {
        $path = realpath(__DIR__) . '/_files/ClassesForArgumentSequence.php';
        $this->_fixturePath = str_replace('\\', '/', $path);
        $this->_validator = new \Magento\Framework\Code\Validator\ArgumentSequence();

        /** Build internal cache */
        $this->_validator->validate('\ArgumentSequence\ParentClass');
    }

    public function testValidSequence()
    {
        $this->assertTrue($this->_validator->validate('\ArgumentSequence\ValidChildClass'));
    }

    public function testInvalidSequence()
    {
        $expectedSequence = '$contextObject, $parentRequiredObject, $parentRequiredScalar, ' .
            '$childRequiredObject, $childRequiredScalar, $parentOptionalObject, $data, $parentOptionalScalar, ' .
            '$childOptionalObject, $childOptionalScalar';

        $actualSequence = '$contextObject, $childRequiredObject, $parentRequiredObject, $parentRequiredScalar, ' .
            '$childRequiredScalar, $parentOptionalObject, $data, $parentOptionalScalar, ' .
            '$childOptionalObject, $childOptionalScalar';

        $message = 'Incorrect argument sequence in class %s in ' .
            $this->_fixturePath .
            PHP_EOL .
            'Required: %s' .
            PHP_EOL .
            'Actual  : %s' .
            PHP_EOL;
        $message = sprintf($message, '\ArgumentSequence\InvalidChildClass', $expectedSequence, $actualSequence);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);
        $this->expectExceptionMessage($message);
        $this->_validator->validate('\ArgumentSequence\InvalidChildClass');
    }
}
