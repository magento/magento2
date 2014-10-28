<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Code\Validator;


require_once '_files/ClassesForArgumentSequence.php';
class ArgumentSequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Code\Validator\ArgumentSequence
     */
    protected $_validator;

    /**
     * @var string
     */
    protected $_fixturePath;

    protected function setUp()
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
        $this->setExpectedException('\Magento\Framework\Code\ValidationException', $message);
        $this->_validator->validate('\ArgumentSequence\InvalidChildClass');
    }
}
