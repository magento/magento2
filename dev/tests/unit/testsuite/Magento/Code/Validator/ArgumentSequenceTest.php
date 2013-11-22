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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Code\Validator;

require_once ('_files/ClassesForArgumentSequence.php');

class ArgumentSequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Code\Validator\ArgumentSequence
     */
    protected $_validator;

    /**
     * @var string
     */
    protected $_fixturePath;

    protected function setUp()
    {
        $this->_fixturePath = realpath(__DIR__)
            . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'ClassesForArgumentSequence.php';
        $this->_validator = $this->getMock('Magento\Code\Validator\ArgumentSequence', array('_isAllowedType'));
        $this->_validator->expects($this->any())->method('_isAllowedType')->will($this->returnValue(true));
    }

    public function testValidSequence()
    {
        $this->assertTrue($this->_validator->validate('\ArgumentSequence\ValidChildClass'));
    }

    /**
     * @dataProvider invalidSequenceDataProvider
     */
    public function testInvalidSequence($className, $expectedSequence)
    {
        $message = 'Incorrect argument sequence in class %s in ' . $this->_fixturePath . PHP_EOL
            . 'Required: %s' . PHP_EOL;
        $message = sprintf($message, $className, $expectedSequence);
        try {
            $this->_validator->validate($className);
        } catch (\Magento\Code\ValidationException $exception) {
            $this->assertStringStartsWith($message, $exception->getMessage());
            return;
        }
        $this->fail('Failed asserting that exception of type "\Magento\Code\ValidationException" is thrown');
    }

    /**
     * @return array
     */
    public function invalidSequenceDataProvider()
    {
        $expectedSequence = '$contextObject, $parentRequiredObject, $parentRequiredScalar, '
            . '$childRequiredObject, $childRequiredScalar, $parentOptionalObject, $data, $parentOptionalScalar, '
            . '$childOptionalObject, $childOptionalScalar';

        $rule04 = '$contextObject, $parentRequiredObject, $parentRequiredScalar, $childRequiredObject, $argument, '
            . '$childRequiredScalar, $parentOptionalObject, $data, $parentOptionalScalar, '
            . '$childOptionalObject, $childOptionalScalar';

        $rule06 = '$contextObject, $parentRequiredObject, $parentRequiredScalar, $childRequiredObject, '
            . '$childRequiredScalar, $parentOptionalObject, $data, $parentOptionalScalar, '
            . '$childOptionalObject, $argument, $childOptionalScalar';

        return array(
            'Rule 01' => array('\ArgumentSequence\InvalidChildClassRule01', $expectedSequence),
            'Rule 02' => array('\ArgumentSequence\InvalidChildClassRule02', $expectedSequence),
            'Rule 03' => array('\ArgumentSequence\InvalidChildClassRule03', $expectedSequence),
            'Rule 04' => array('\ArgumentSequence\InvalidChildClassRule04', $rule04),
            'Rule 05' => array('\ArgumentSequence\InvalidChildClassRule05', $expectedSequence),
            'Rule 06' => array('\ArgumentSequence\InvalidChildClassRule06', $rule06),
            'Rule 07' => array('\ArgumentSequence\InvalidChildClassRule07', $expectedSequence),
            'Rule 08' => array('\ArgumentSequence\InvalidChildClassRule08', $expectedSequence),
            'Rule 09' => array('\ArgumentSequence\InvalidChildClassRule09', $expectedSequence),
            'Rule 10' => array('\ArgumentSequence\InvalidChildClassRule10', $expectedSequence),
        );
    }
}
 