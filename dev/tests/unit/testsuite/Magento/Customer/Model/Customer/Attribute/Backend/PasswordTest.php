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

namespace Magento\Customer\Model\Customer\Attribute\Backend;

use Magento\Framework\Stdlib\String;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Password
     */
    protected $testable;

    public function setUp()
    {
        $logger = $this->getMockBuilder('Magento\Framework\Logger')->disableOriginalConstructor()->getMock();
        $string = new String();
        /** @var \Magento\Framework\Logger $logger */
        $this->testable = new Password($logger, $string);
    }

    public function testValidatePositive()
    {
        $password = 'password';
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getPassword', 'getPasswordConfirm'))
            ->getMock();

        $object->expects($this->once())->method('getPassword')->will($this->returnValue($password));
        $object->expects($this->once())->method('getPasswordConfirm')->will($this->returnValue($password));
        /** @var \Magento\Framework\Object $object */

        $this->assertTrue($this->testable->validate($object));
    }

    public function passwordNegativeDataProvider()
    {
        return array(
            'less-then-6-char' => array('less6'),
            'with-space-prefix' => array(' normal_password'),
            'with-space-suffix' => array('normal_password '),
        );
    }

    /**
     * @dataProvider passwordNegativeDataProvider
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testBeforeSaveNegative($password)
    {
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getPassword'))
            ->getMock();

        $object->expects($this->once())->method('getPassword')->will($this->returnValue($password));
        /** @var \Magento\Framework\Object $object */

        $this->testable->beforeSave($object);
    }

    public function testBeforeSavePositive()
    {
        $password = 'more-then-6';
        $passwordHash = 'password-hash';
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getPassword', 'setPasswordHash', 'hashPassword'))
            ->getMock();

        $object->expects($this->once())->method('getPassword')->will($this->returnValue($password));
        $object->expects($this->once())->method('hashPassword')->will($this->returnValue($passwordHash));
        $object->expects($this->once())->method('setPasswordHash')->with($passwordHash)->will($this->returnSelf());
        /** @var \Magento\Framework\Object $object */

        $this->testable->beforeSave($object);
    }
}
