<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Block\Adminhtml\Block\Edit;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemStore;

    /**
     * @var array
     */
    protected $storeValues = [1, 2];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $field;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wysiwygConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldSet;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $action = 'test';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Cms\Block\Adminhtml\Block\Edit\Form
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resolver;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validator;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_resolver = $this->getMock(
            'Magento\Framework\View\Element\Template\File\Resolver',
            [],
            [],
            '',
            false
        );

        $this->_validator = $this->getMock(
            'Magento\Framework\View\Element\Template\File\Validator',
            [],
            [],
            '',
            false
        );

        $this->model = $this->getMock('Magento\Cms\Model\Block', ['getBlockId', 'setStoreId'], [], '', false);

        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->registry->expects($this->once())->method('registry')->with('cms_block')->willReturn($this->model);

        $this->systemStore = $this->getMock('Magento\Store\Model\System\Store', [], [], '', false);
        $this->systemStore->expects($this->any())
            ->method('getStoreValuesForForm')
            ->with(false, true)
            ->willReturn($this->storeValues);

        $this->field = $this->getMock('Magento\Framework\Data\Form\Element\AbstractElement', [], [], '', false);
        $this->config = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $this->fieldSet = $this->getMock('Magento\Framework\Data\Form\Element\Fieldset', [], [], '', false);
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $this->urlBuilder = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $this->rootDirectory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\ReadInterface',
            [],
            [],
            '',
            false
        );
        $this->layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);

        $this->fileSystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->fileSystem->expects($this->atLeastOnce())->method('getDirectoryRead')->willReturn($this->rootDirectory);

        $this->wysiwygConfig = $this->getMock('Magento\Cms\Model\Wysiwyg\Config', [], [], '', false);
        $this->wysiwygConfig->expects($this->once())->method('getConfig')->willReturn($this->config);

        $this->form = $this->getMock('Magento\Framework\Data\Form', [], [], '', false);
        $this->form->expects($this->once())->method('addFieldset')->with(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        )->willReturn($this->fieldSet);

        $this->formFactory = $this->getMock('\Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['id' => 'edit_form', 'action' => $this->action, 'method' => 'post']])
            ->willReturn($this->form);

        $this->context = $this->getMock('Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->context->expects($this->once())->method('getEventManager')->willReturn($this->eventManager);
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->context->expects($this->once())->method('getStoreManager')->willReturn($this->storeManager);
        $this->context->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->context->expects($this->once())->method('getAppState')->willReturn($this->appState);
        $this->context->expects($this->once())->method('getFilesystem')->willReturn($this->fileSystem);
        $this->context->expects($this->once())->method('getLogger')->willReturn($this->logger);
        $this->context->expects($this->once())->method('getLayout')->willReturn($this->layout);
        $this->context->expects($this->once())->method('getResolver')->willReturn($this->_resolver);
        $this->context->expects($this->once())->method('getValidator')->willReturn($this->_validator);

        /** @var \Magento\Cms\Block\Adminhtml\Block\Edit\Form $block */
        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                'Magento\Cms\Block\Adminhtml\Block\Edit\Form',
                [
                    'formFactory' => $this->formFactory,
                    'registry' => $this->registry,
                    'wysiwygConfig' => $this->wysiwygConfig,
                    'context' => $this->context,
                    'systemStore' => $this->systemStore
                ]
            );
        $this->block->setData('action', $this->action);
    }

    /**
     * Test prepare form model has no block id and single store mode is on
     *
     * @return void
     */
    public function testPrepareFormModelHasNoBlockIdAndSingleStoreMode()
    {
        $blockId = null;
        $storeId = 1;

        $this->model->expects($this->once())->method('getBlockId')->willReturn($blockId);
        $this->model->expects($this->once())->method('setStoreId')->with($storeId);

        $this->store->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);

        $this->storeManager->expects($this->once())->method('isSingleStoreMode')->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->with(true)->willReturn($this->store);

        $this->fieldSet->expects($this->at(2))
            ->method('addField')
            ->with(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $storeId]
            )
            ->willReturn($this->field);

        $this->block->toHtml();
    }

    /**
     * Test prepare form model has block id and signle store mode is off
     *
     * @return void
     */
    public function testPrepareFormModelHasBlockIdAndNonSingleStoreMode()
    {
        $blockId = 'id';

        $renderer = $this->getMock(
            'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element',
            [],
            [],
            '',
            false
        );

        $this->model->expects($this->once())->method('getBlockId')->willReturn($blockId);

        $this->layout->expects($this->once())
            ->method('createBlock')
            ->with('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element')
            ->willReturn($renderer);

        $this->field->expects($this->once())->method('setRenderer')->with($renderer);

        $this->fieldSet->expects($this->at(0))->method('addField')->with('block_id', 'hidden', ['name' => 'block_id']);
        $this->fieldSet->expects($this->at(3))
            ->method('addField')
            ->with(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->storeValues
                ]
            )->willReturn($this->field);

        $this->block->toHtml();
    }
}
