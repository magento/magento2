<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Model\Config\Source\Email;

class TemplateTest extends \PHPUnit\Framework\TestCase
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
        $this->_coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $this->_emailConfig = $this->createMock(\Magento\Email\Model\Template\Config::class);
        $this->_templatesFactory = $this->createMock(
            \Magento\Email\Model\ResourceModel\Template\CollectionFactory::class
        );
        $this->_model = new \Magento\Config\Model\Config\Source\Email\Template(
            $this->_coreRegistry,
            $this->_templatesFactory,
            $this->_emailConfig
        );
    }

    public function testToOptionArray()
    {
        $collection = $this->createMock(\Magento\Email\Model\ResourceModel\Template\Collection::class);
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
