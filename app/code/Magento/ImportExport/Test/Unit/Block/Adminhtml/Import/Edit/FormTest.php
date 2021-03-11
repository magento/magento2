<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Import\Edit;

class FormTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Basic import model
     *
     * @var \Magento\ImportExport\Model\Import|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_importModel;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $form;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formFactory = $this->getMockBuilder(\Magento\Framework\Data\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importModel = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_entityFactory = $this->getMockBuilder(\Magento\ImportExport\Model\Source\Import\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder(
            \Magento\ImportExport\Model\Source\Import\Behavior\Factory::class
        )->disableOriginalConstructor()->getMock();

        $this->form = $this->getMockBuilder(\Magento\ImportExport\Block\Adminhtml\Import\Edit\Form::class)
            ->setConstructorArgs([
                $context,
                $registry,
                $formFactory,
                $this->_importModel,
                $this->_entityFactory,
                $this->_behaviorFactory,
            ])
            ->getMock();
    }

    /**
     * Test for protected method prepareForm()
     *
     * @todo to implement it.
     */
    public function testPrepareForm()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
