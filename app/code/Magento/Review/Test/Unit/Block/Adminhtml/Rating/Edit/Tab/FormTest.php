<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block\Adminhtml\Rating\Edit\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Review\Model\Rating
     */
    protected $rating;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection
     */
    protected $ratingOptionCollection;

    /**
     * @var \Magento\Review\Model\Rating\Option
     */
    protected $optionRating;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $element;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $directoryReadInterface;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Review\Model\Rating\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form
     */
    protected $block;

    protected function setUp(): void
    {
        $this->ratingOptionCollection = $this->createMock(
            \Magento\Review\Model\ResourceModel\Rating\Option\Collection::class
        );
        $this->element = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\Text::class,
            ['setValue', 'setIsChecked']
        );
        $this->session = $this->createPartialMock(
            \Magento\Framework\Session\Generic::class,
            ['getRatingData', 'setRatingData']
        );
        $this->rating = $this->createPartialMock(\Magento\Review\Model\Rating::class, ['getId', 'getRatingCodes']);
        $this->optionRating = $this->createMock(\Magento\Review\Model\Rating\Option::class);
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->form = $this->createPartialMock(
            \Magento\Framework\Data\Form::class,
            ['setForm', 'addFieldset', 'addField', 'setRenderer', 'getElement', 'setValues']
        );
        $this->directoryReadInterface = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->formFactory = $this->createMock(\Magento\Framework\Data\FormFactory::class);
        $this->optionFactory = $this->createPartialMock(\Magento\Review\Model\Rating\OptionFactory::class, ['create']);
        $this->systemStore = $this->createMock(\Magento\Store\Model\System\Store::class);
        $this->viewFileSystem = $this->createMock(\Magento\Framework\View\FileSystem::class);
        $this->fileSystem = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryRead']);

        $this->rating->expects($this->any())->method('getId')->willReturn('1');
        $this->ratingOptionCollection->expects($this->any())->method('addRatingFilter')->willReturnSelf();
        $this->ratingOptionCollection->expects($this->any())->method('load')->willReturnSelf();
        $this->ratingOptionCollection->expects($this->any())->method('getItems')
            ->willReturn([$this->optionRating]);
        $this->optionRating->expects($this->any())->method('getResourceCollection')
            ->willReturn($this->ratingOptionCollection);
        $this->store->expects($this->any())->method('getId')->willReturn('0');
        $this->store->expects($this->any())->method('getName')->willReturn('store_name');
        $this->element->expects($this->any())->method('setValue')->willReturnSelf();
        $this->element->expects($this->any())->method('setIsChecked')->willReturnSelf();
        $this->form->expects($this->any())->method('setForm')->willReturnSelf();
        $this->form->expects($this->any())->method('addFieldset')->willReturnSelf();
        $this->form->expects($this->any())->method('addField')->willReturnSelf();
        $this->form->expects($this->any())->method('setRenderer')->willReturnSelf();
        $this->form->expects($this->any())->method('setValues')->willReturnSelf();
        $this->optionFactory->expects($this->any())->method('create')->willReturn($this->optionRating);
        $this->systemStore->expects($this->any())->method('getStoreCollection')
            ->willReturn(['0' => $this->store]);
        $this->formFactory->expects($this->any())->method('create')->willReturn($this->form);
        $this->viewFileSystem->expects($this->any())->method('getTemplateFileName')
            ->willReturn('template_file_name.html');
        $this->fileSystem->expects($this->any())->method('getDirectoryRead')
            ->willReturn($this->directoryReadInterface);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form::class,
            [
                'registry' => $this->registry,
                'formFactory' => $this->formFactory,
                'optionFactory' => $this->optionFactory,
                'systemStore' => $this->systemStore,
                'session' => $this->session,
                'viewFileSystem' => $this->viewFileSystem,
                'filesystem' => $this->fileSystem,
            ]
        );
    }

    public function testToHtmlSessionRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->willReturn($this->rating);
        $this->form->expects($this->at(5))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->at(11))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->at(14))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->at(15))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->any())->method('getElement')->willReturn(false);
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->session->expects($this->any())->method('getRatingData')->willReturn($ratingCodes);
        $this->session->expects($this->any())->method('setRatingData')->willReturnSelf();
        $this->block->toHtml();
    }

    public function testToHtmlCoreRegistryRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->willReturn($this->rating);
        $this->form->expects($this->at(5))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->at(11))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->at(14))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->at(15))->method('getElement')->willReturn($this->element);
        $this->form->expects($this->any())->method('getElement')->willReturn(false);
        $this->session->expects($this->any())->method('getRatingData')->willReturn(false);
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->rating->expects($this->any())->method('getRatingCodes')->willReturn($ratingCodes);
        $this->block->toHtml();
    }

    public function testToHtmlWithoutRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->willReturn(false);
        $this->systemStore->expects($this->atLeastOnce())->method('getStoreCollection')
            ->willReturn(['0' => $this->store]);
        $this->formFactory->expects($this->any())->method('create')->willReturn($this->form);
        $this->viewFileSystem->expects($this->any())->method('getTemplateFileName')
            ->willReturn('template_file_name.html');
        $this->fileSystem->expects($this->any())->method('getDirectoryRead')
            ->willReturn($this->directoryReadInterface);
        $this->block->toHtml();
    }
}
