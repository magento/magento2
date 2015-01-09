<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $_templateMock;

    public function setUp()
    {
        $this->_objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface');
        $this->_templateMock = $this->getMock('\Magento\Framework\Mail\TemplateInterface');
    }

    /**
     * @covers \Magento\Framework\Mail\Template\Factory::get
     * @covers \Magento\Framework\Mail\Template\Factory::__construct
     */
    public function testGet()
    {
        $model = new \Magento\Framework\Mail\Template\Factory($this->_objectManagerMock);

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Mail\TemplateInterface', ['data' => ['template_id' => 'identifier']])
            ->will($this->returnValue($this->_templateMock));

        $this->assertInstanceOf('\Magento\Framework\Mail\TemplateInterface', $model->get('identifier'));
    }
}
