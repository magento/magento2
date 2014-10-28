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
namespace Magento\Payment\Model\Method\Specification;

/**
 * Factory Test
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Payment\Model\Method\Specification\Factory
     */
    protected $factory;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->factory = $objectManagerHelper->getObject(
            'Magento\Payment\Model\Method\Specification\Factory',
            array('objectManager' => $this->objectManagerMock)
        );
    }

    public function testCreateMethod()
    {
        $className = 'Magento\Payment\Model\Method\SpecificationInterface';
        $methodMock = $this->getMock($className);
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $className
        )->will(
            $this->returnValue($methodMock)
        );

        $this->assertEquals($methodMock, $this->factory->create($className));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specification must implement SpecificationInterface
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';
        $methodMock = $this->getMock($className);
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $className
        )->will(
            $this->returnValue($methodMock)
        );

        $this->factory->create($className);
    }
}
