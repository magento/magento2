<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Import\Edit;

class FormTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Basic import model
     *
     * @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importModel;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    public function setUp()
    {
        $context = $this->getMockBuilder('\Magento\Backend\Block\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $formFactory = $this->getMockBuilder('\Magento\Framework\Data\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importModel = $this->getMockBuilder('\Magento\ImportExport\Model\Import')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_entityFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Source\Import\EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Source\Import\Behavior\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->getMockBuilder('\Magento\ImportExport\Block\Adminhtml\Import\Edit\Form')
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
