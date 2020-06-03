<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Source\Email;

use Magento\Config\Model\Config\Source\Email\Template;
use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Template.
 */
class TemplateTest extends TestCase
{
    /**
     * @var Template
     */
    protected $_model;

    /**
     * @var Registry|MockObject
     */
    protected $_coreRegistry;

    /**
     * @var Config|MockObject
     */
    protected $_emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Email\Template\CollectionFactory
     */
    protected $_templatesFactory;

    protected function setUp(): void
    {
        $this->_coreRegistry = $this->createMock(Registry::class);
        $this->_emailConfig = $this->createMock(Config::class);
        $this->_templatesFactory = $this->createMock(
            CollectionFactory::class
        );
        $this->_model = new Template(
            $this->_coreRegistry,
            $this->_templatesFactory,
            $this->_emailConfig
        );
    }

    public function testToOptionArray()
    {
        $collection = $this->createMock(Collection::class);
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
            ],
            [
                'value' => 'template_one',
                'label' => 'Template One',
            ],
            [
                'value' => 'template_two',
                'label' => 'Template Two',
            ],
        ];
        $this->_model->setPath('template/new');
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
