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
namespace Magento\Framework\Filter;

class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filter\AbstractFactory
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_invokableList = array(
        'sprintf' => 'Magento\Framework\Filter\Sprintf',
        'template' => 'Magento\Framework\Filter\Template',
        'arrayFilter' => 'Magento\Framework\Filter\ArrayFilter'
    );

    /**
     * @var array
     */
    protected $_sharedList = array(
        'Magento\Framework\Filter\Template' => true,
        'Magento\Framework\Filter\ArrayFilter' => false
    );

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    public function setUp()
    {
        $this->_objectManager = $this->getMockForAbstractClass(
            '\Magento\Framework\ObjectManager',
            array(),
            '',
            true,
            true,
            true,
            array('create')
        );

        $this->_factory = $this->getMockForAbstractClass(
            'Magento\Framework\Filter\AbstractFactory',
            array('objectManger' => $this->_objectManager)
        );
        $property = new \ReflectionProperty('Magento\Framework\Filter\AbstractFactory', 'invokableClasses');
        $property->setAccessible(true);
        $property->setValue($this->_factory, $this->_invokableList);

        $property = new \ReflectionProperty('Magento\Framework\Filter\AbstractFactory', 'shared');
        $property->setAccessible(true);
        $property->setValue($this->_factory, $this->_sharedList);
    }

    /**
     * @dataProvider canCreateFilterDataProvider
     * @param string $alias
     * @param bool $expectedResult
     */
    public function testCanCreateFilter($alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_factory->canCreateFilter($alias));
    }

    /**
     * @return array
     */
    public function canCreateFilterDataProvider()
    {
        return array(array('arrayFilter', true), array('notExist', false));
    }

    /**
     * @dataProvider isSharedDataProvider
     * @param string $alias
     * @param bool $expectedResult
     */
    public function testIsShared($alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_factory->isShared($alias));
    }

    /**
     * @return array
     */
    public function isSharedDataProvider()
    {
        return array(
            'shared' => array('Magento\Framework\Filter\Template', true),
            'not shared' => array('Magento\Framework\Filter\ArrayFilter', false),
            'default value' => array('Magento\Framework\Filter\Sprintf', true)
        );
    }

    /**
     * @dataProvider createFilterDataProvider
     * @param string $alias
     * @param array $arguments
     * @param bool $isShared
     */
    public function testCreateFilter($alias, $arguments, $isShared)
    {
        $property = new \ReflectionProperty('Magento\Framework\Filter\AbstractFactory', 'sharedInstances');
        $property->setAccessible(true);

        $filterMock = $this->getMock('FactoryInterface', array('filter'));
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $this->equalTo($this->_invokableList[$alias]),
            $this->equalTo($arguments)
        )->will(
            $this->returnValue($filterMock)
        );

        $this->assertEquals($filterMock, $this->_factory->createFilter($alias, $arguments));
        if ($isShared) {
            $sharedList = $property->getValue($this->_factory);
            $this->assertTrue(array_key_exists($alias, $sharedList));
            $this->assertEquals($filterMock, $sharedList[$alias]);
        } else {
            $this->assertEmpty($property->getValue($this->_factory));
        }
    }

    /**
     * @return array
     */
    public function createFilterDataProvider()
    {
        return array(
            'not shared with args' => array('arrayFilter', array('123', '231'), false),
            'not shared without args' => array('arrayFilter', array(), true),
            'shared' => array('template', array(), true),
            'default shared' => array('sprintf', array(), true)
        );
    }
}
