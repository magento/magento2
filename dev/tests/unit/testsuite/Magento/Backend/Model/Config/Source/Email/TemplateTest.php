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
namespace Magento\Backend\Model\Config\Source\Email;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Source\Email\Template
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_emailConfig;

    /**
     * @var /Magento\Core\Model\Resource\Email\Template\CollectionFactory
     */
    protected $_templatesFactory;

    protected function setUp()
    {
        $this->_coreRegistry = $this->getMock('Magento\Framework\Registry', array(), array(), '', false, false);
        $this->_emailConfig = $this->getMock('Magento\Email\Model\Template\Config', array(), array(), '', false);
        $this->_templatesFactory = $this->getMock(
            'Magento\Email\Model\Resource\Template\CollectionFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Backend\Model\Config\Source\Email\Template(
            $this->_coreRegistry,
            $this->_templatesFactory,
            $this->_emailConfig
        );
    }

    public function testToOptionArray()
    {
        $collection = $this->getMock('Magento\Email\Model\Resource\Template\Collection', array(), array(), '', false);
        $collection->expects(
            $this->once()
        )->method(
            'toOptionArray'
        )->will(
            $this->returnValue(
                array(
                    array('value' => 'template_one', 'label' => 'Template One'),
                    array('value' => 'template_two', 'label' => 'Template Two')
                )
            )
        );
        $this->_coreRegistry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'config_system_email_template'
        )->will(
            $this->returnValue($collection)
        );
        $this->_emailConfig->expects(
            $this->once()
        )->method(
            'getTemplateLabel'
        )->with(
            'template_new'
        )->will(
            $this->returnValue('Template New')
        );
        $expectedResult = array(
            array('value' => 'template_new', 'label' => 'Template New (Default)'),
            array('value' => 'template_one', 'label' => 'Template One'),
            array('value' => 'template_two', 'label' => 'Template Two')
        );
        $this->_model->setPath('template/new');
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
