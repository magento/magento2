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
namespace Magento\Sales\Model\Order\Pdf\Total;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Total\Factory
     */
    protected $_factory;

    public function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false);
        $this->_factory = new \Magento\Sales\Model\Order\Pdf\Total\Factory($this->_objectManager);
    }

    /**
     * @param mixed $class
     * @param array $arguments
     * @param string $expectedClassName
     * @dataProvider createDataProvider
     */
    public function testCreate($class, $arguments, $expectedClassName)
    {
        $createdModel = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            array(),
            array(),
            (string)$class,
            false
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedClassName,
            $arguments
        )->will(
            $this->returnValue($createdModel)
        );

        $actual = $this->_factory->create($class, $arguments);
        $this->assertSame($createdModel, $actual);
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return array(
            'default model' => array(
                null,
                array('param1', 'param2'),
                'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal'
            ),
            'custom model' => array('custom_class', array('param1', 'param2'), 'custom_class')
        );
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage The PDF total model TEST must be or extend
     * \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal.
     */
    public function testCreateException()
    {
        $this->_factory->create('TEST');
    }
}
