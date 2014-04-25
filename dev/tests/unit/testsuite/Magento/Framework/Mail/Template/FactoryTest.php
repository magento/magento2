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
        $this->_objectManagerMock = $this->getMock('\Magento\Framework\ObjectManager');
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
            ->with('Magento\Framework\Mail\TemplateInterface', array('data' => array('template_id' => 'identifier')))
            ->will($this->returnValue($this->_templateMock));

        $this->assertInstanceOf('\Magento\Framework\Mail\TemplateInterface', $model->get('identifier'));
    }
}
