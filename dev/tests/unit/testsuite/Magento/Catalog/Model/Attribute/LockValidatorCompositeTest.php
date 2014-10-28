<?php
/**
 *
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
namespace Magento\Catalog\Model\Attribute;

class LockValidatorCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Attribute\LockValidatorComposite
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('\Magento\Framework\ObjectManager');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCompositionsWithInvalidValidatorInstance()
    {
        $validators = array('Magento\Catalog\Model');
        $this->model = new \Magento\Catalog\Model\Attribute\LockValidatorComposite(
            $this->objectManagerMock,
            $validators
        );
    }

    public function testValidateWithValidValidatorInstance()
    {
        $validators = array('Magento\Catalog\Model\Attribute\LockValidatorComposite');
        $lockValidatorMock = $this->getMock('Magento\Catalog\Model\Attribute\LockValidatorInterface');
        $this->objectManagerMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'Magento\Catalog\Model\Attribute\LockValidatorComposite'
        )->will(
            $this->returnValue($lockValidatorMock)
        );

        $this->model = new \Magento\Catalog\Model\Attribute\LockValidatorComposite(
            $this->objectManagerMock,
            $validators
        );
        $abstractModelHelper = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false, false);
        $lockValidatorMock->expects($this->once())->method('validate')->with($abstractModelHelper);
        $this->model->validate($abstractModelHelper);
    }
}
