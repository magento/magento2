<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Model\Config\Source\Email;

/**
 * Test class for Template.
 */
class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Email\Template\CollectionFactory
     */
    protected $_templatesFactory;

    protected function setUp(): void
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
        )->willReturn(
            
                [
                    ['value' => 'template_one', 'label' => 'Template One'],
                    ['value' => 'template_two', 'label' => 'Template Two'],
                ]
            
        );
        $this->_coreRegistry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'config_system_email_template'
        )->willReturn(
            $collection
        );
        $this->_emailConfig->expects(
            $this->once()
        )->method(
            'getTemplateLabel'
        )->with(
            'template_new'
        )->willReturn(
            'Template New'
        );
        $expectedResult = [
            [
                'value' => 'template_new',
                'label' => 'Template New (Default)',
                '__disableTmpl' => true
            ],
            [
                'value' => 'template_one',
                'label' => 'Template One',
                '__disableTmpl' => true
            ],
            [
                'value' => 'template_two',
                'label' => 'Template Two',
                '__disableTmpl' => true
            ],
        ];
        $this->_model->setPath('template/new');
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
