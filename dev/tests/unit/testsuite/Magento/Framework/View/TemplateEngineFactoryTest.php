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
namespace Magento\Framework\View;

class TemplateEngineFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var  \Magento\Framework\View\TemplateEngineFactory */
    protected $_factory;

    /**
     * Setup a factory to test with an mocked object manager.
     */
    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_factory = new TemplateEngineFactory(
            $this->_objectManagerMock,
            array('test' => 'Fixture\Module\Model\TemplateEngine')
        );
    }

    public function testCreateKnownEngine()
    {
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Fixture\Module\Model\TemplateEngine'
        )->will(
            $this->returnValue($engine)
        );
        $this->assertSame($engine, $this->_factory->create('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown template engine type: 'non_existing'
     */
    public function testCreateUnknownEngine()
    {
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_factory->create('non_existing');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Fixture\Module\Model\TemplateEngine has to implement the template engine interface
     */
    public function testCreateInvalidEngine()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Fixture\Module\Model\TemplateEngine'
        )->will(
            $this->returnValue(new \stdClass())
        );
        $this->_factory->create('test');
    }
}
