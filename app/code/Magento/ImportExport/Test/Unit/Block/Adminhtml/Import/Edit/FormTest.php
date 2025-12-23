<?php declare(strict_types=1);

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\ImportExport\Block\Adminhtml\Import\Edit\Form;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();

        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $formFactory = $this->createMock(FormFactory::class);
        $this->_importModel = $this->createMock(Import::class);
        $this->_entityFactory = $this->createMock(EntityFactory::class);
        $this->_behaviorFactory = $this->createMock(Factory::class);

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
