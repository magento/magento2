<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\ImportExport\Block\Adminhtml\Import\Edit\Form;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory;
use Magento\ImportExport\Model\Source\Import\EntityFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{

    /**
     * Basic import model
     *
     * @var Import|MockObject
     */
    protected $_importModel;

    /**
     * @var EntityFactory|MockObject
     */
    protected $_entityFactory;

    /**
     * @var Factory|MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var Form|MockObject
     */
    protected $form;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importModel = $this->getMockBuilder(Import::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_entityFactory = $this->getMockBuilder(EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder(
            Factory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->getMockBuilder(Form::class)
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
        $this->markTestSkipped('This test has not been implemented yet.');
    }
}
