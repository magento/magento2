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
namespace Magento\DesignEditor\Model\Url;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Model\Url\Factory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\DesignEditor\Model\Url\Factory($this->_objectManager);
    }

    public function testConstruct()
    {
        $this->assertAttributeInstanceOf('Magento\Framework\ObjectManager', '_objectManager', $this->_model);
    }

    public function testReplaceClassName()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'configure'
        )->with(
            array('preferences' => array('Magento\Framework\UrlInterface' => 'TestClass'))
        );

        $this->assertEquals($this->_model, $this->_model->replaceClassName('TestClass'));
    }

    public function testCreate()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\UrlInterface',
            array()
        )->will(
            $this->returnValue('ModelInstance')
        );

        $this->assertEquals('ModelInstance', $this->_model->create());
    }
}
