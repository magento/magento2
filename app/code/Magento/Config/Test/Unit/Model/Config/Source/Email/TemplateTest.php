<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Config\Test\Unit\Model\Config\Source\Email;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
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
     * @var \Magento\Email\Model\ResourceModel\Email\Template\CollectionFactory
     */
    protected $_templatesFactory;

    protected function setUp()
    {
        $this->_coreRegistry = $this->getMock('Magento\Framework\Registry', [], [], '', false, false);
        $this->_emailConfig = $this->getMock('Magento\Email\Model\Template\Config', [], [], '', false);
        $this->_templatesFactory = $this->getMock(
            'Magento\Email\Model\ResourceModel\Template\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Config\Model\Config\Source\Email\Template(
            $this->_coreRegistry,
            $this->_templatesFactory,
            $this->_emailConfig
        );
    }

    public function testToOptionArray()
    {
        $collection = $this->getMock('Magento\Email\Model\ResourceModel\Template\Collection', [], [], '', false);
        $collection->expects(
            $this->once()
        )->method(
            'toOptionArray'
        )->will(
            $this->returnValue(
                [
                    ['value' => 'template_one', 'label' => 'Template One'],
                    ['value' => 'template_two', 'label' => 'Template Two'],
                ]
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
        $expectedResult = [
            ['value' => 'template_new', 'label' => 'Template New (Default)'],
            ['value' => 'template_one', 'label' => 'Template One'],
            ['value' => 'template_two', 'label' => 'Template Two'],
        ];
        $this->_model->setPath('template/new');
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
