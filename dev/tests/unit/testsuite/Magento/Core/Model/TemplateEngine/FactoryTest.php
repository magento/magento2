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

namespace Magento\Core\Model\TemplateEngine;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_model = new Factory($this->_objectManager, array(
            'test' => 'Fixture\Module\Model\TemplateEngine',
        ));
    }

    public function testCreateKnownEngine()
    {
        $engine = $this->getMock('Magento\Core\Model\TemplateEngine\EngineInterface');
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with('Fixture\Module\Model\TemplateEngine')
            ->will($this->returnValue($engine))
        ;
        $this->assertSame($engine, $this->_model->create('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown template engine 'non_existing'
     */
    public function testCreateUnknownEngine()
    {
        $this->_objectManager->expects($this->never())->method('create');
        $this->_model->create('non_existing');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Fixture\Module\Model\TemplateEngine has to implement the template engine interface
     */
    public function testCreateInvalidEngine()
    {
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with('Fixture\Module\Model\TemplateEngine')
            ->will($this->returnValue(new \stdClass()))
        ;
        $this->_model->create('test');
    }
}
