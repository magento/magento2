<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block\Adminhtml\Rating\Edit\Tab;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\FileSystem as FilesystemView;
use Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form as RatingEditForm;
use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option;
use Magento\Review\Model\Rating\OptionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Collection;
use Magento\Store\Model\Store;
use Magento\Store\Model\System\Store as SystemStore;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Rating
     */
    protected $rating;

    /**
     * @var Collection
     */
    protected $ratingOptionCollection;

    /**
     * @var Option
     */
    protected $optionRating;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var Text
     */
    protected $element;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var ReadInterface
     */
    protected $directoryReadInterface;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @var Generic
     */
    protected $session;

    /**
     * @var FilesystemView
     */
    protected $viewFileSystem;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var RatingEditForm
     */
    protected $block;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->ratingOptionCollection = $this->createMock(
            Collection::class
        );
        $this->element = $this->createPartialMockWithReflection(
            Text::class,
            ['setValue', 'setIsChecked']
        );
        $this->session = $this->createPartialMockWithReflection(
            Generic::class,
            ['getRatingData', 'setRatingData']
        );
        $this->rating = $this->createPartialMockWithReflection(
            Rating::class,
            ['getRatingCodes', 'getId']
        );
        $this->optionRating = $this->createMock(Option::class);
        $this->store = $this->createMock(Store::class);
        $this->form = $this->createPartialMockWithReflection(
            Form::class,
            ['setForm', 'setRenderer', 'addFieldset', 'addField', 'getElement', 'setValues']
        );
        $this->directoryReadInterface = $this->createMock(ReadInterface::class);
        $this->registry = $this->createMock(Registry::class);
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->optionFactory = $this->createPartialMock(OptionFactory::class, ['create']);
        $this->systemStore = $this->createMock(SystemStore::class);
        $this->viewFileSystem = $this->createMock(FilesystemView::class);
        $this->fileSystem = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);

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
        $objects = [
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)]
        ];
        $objectManagerHelper->prepareObjectManager($objects);
        
        $this->block = $objectManagerHelper->getObject(
            RatingEditForm::class,
            [
                'registry' => $this->registry,
                'formFactory' => $this->formFactory,
                'optionFactory' => $this->optionFactory,
                'systemStore' => $this->systemStore,
                'session' => $this->session,
                'viewFileSystem' => $this->viewFileSystem,
                'filesystem' => $this->fileSystem
            ]
        );
    }

    /**
     * @return void
     */
    public function testToHtmlSessionRatingData(): void
    {
        $this->registry->expects($this->any())->method('registry')->willReturn($this->rating);
        $this->form
            ->method('getElement')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->element,
                null,
                $this->element,
                $this->element,
                $this->element,
                false
            );
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->session->expects($this->any())->method('getRatingData')->willReturn($ratingCodes);
        $this->session->expects($this->any())->method('setRatingData')->willReturnSelf();
        $this->block->toHtml();
    }

    /**
     * @return void
     */
    public function testToHtmlCoreRegistryRatingData(): void
    {
        $this->registry->expects($this->any())->method('registry')->willReturn($this->rating);
        $this->form
            ->method('getElement')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->element,
                null,
                $this->element,
                $this->element,
                $this->element,
                false
            );
        $this->session->expects($this->any())->method('getRatingData')->willReturn(false);
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->rating->expects($this->any())->method('getRatingCodes')->willReturn($ratingCodes);
        $this->block->toHtml();
    }

    /**
     * @return void
     */
    public function testToHtmlWithoutRatingData(): void
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
