<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block\Adminhtml\Rating\Edit\Tab;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\FileSystem as FilesystemView;
use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option;
use Magento\Review\Model\Rating\OptionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Collection;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
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
     * @var \Magento\Store\Model\System\Store
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
     * @var \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form
     */
    protected $block;

    protected function setUp(): void
    {
        $this->ratingOptionCollection = $this->createMock(
            Collection::class
        );
        $this->element = $this->createPartialMock(
            Text::class,
            ['setValue', 'setIsChecked']
        );
        $this->session = $this->createPartialMock(
            Generic::class,
            ['getRatingData', 'setRatingData']
        );
        $this->rating = $this->createPartialMock(Rating::class, ['getId', 'getRatingCodes']);
        $this->optionRating = $this->createMock(Option::class);
        $this->store = $this->createMock(Store::class);
        $this->form = $this->createPartialMock(
            Form::class,
            ['setForm', 'addFieldset', 'addField', 'setRenderer', 'getElement', 'setValues']
        );
        $this->directoryReadInterface = $this->createMock(ReadInterface::class);
        $this->registry = $this->createMock(Registry::class);
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->optionFactory = $this->createPartialMock(OptionFactory::class, ['create']);
        $this->systemStore = $this->createMock(\Magento\Store\Model\System\Store::class);
        $this->viewFileSystem = $this->createMock(FilesystemView::class);
        $this->fileSystem = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);

        $this->rating->expects($this->any())->method('getId')->will($this->returnValue('1'));
        $this->ratingOptionCollection->expects($this->any())->method('addRatingFilter')->will($this->returnSelf());
        $this->ratingOptionCollection->expects($this->any())->method('load')->will($this->returnSelf());
        $this->ratingOptionCollection->expects($this->any())->method('getItems')
            ->will($this->returnValue([$this->optionRating]));
        $this->optionRating->expects($this->any())->method('getResourceCollection')
            ->will($this->returnValue($this->ratingOptionCollection));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('0'));
        $this->store->expects($this->any())->method('getName')->will($this->returnValue('store_name'));
        $this->element->expects($this->any())->method('setValue')->will($this->returnSelf());
        $this->element->expects($this->any())->method('setIsChecked')->will($this->returnSelf());
        $this->form->expects($this->any())->method('setForm')->will($this->returnSelf());
        $this->form->expects($this->any())->method('addFieldset')->will($this->returnSelf());
        $this->form->expects($this->any())->method('addField')->will($this->returnSelf());
        $this->form->expects($this->any())->method('setRenderer')->will($this->returnSelf());
        $this->form->expects($this->any())->method('setValues')->will($this->returnSelf());
        $this->optionFactory->expects($this->any())->method('create')->will($this->returnValue($this->optionRating));
        $this->systemStore->expects($this->any())->method('getStoreCollection')
            ->will($this->returnValue(['0' => $this->store]));
        $this->formFactory->expects($this->any())->method('create')->will($this->returnValue($this->form));
        $this->viewFileSystem->expects($this->any())->method('getTemplateFileName')
            ->will($this->returnValue('template_file_name.html'));
        $this->fileSystem->expects($this->any())->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryReadInterface));

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
        $this->registry->expects($this->any())->method('registry')->will($this->returnValue($this->rating));
        $this->form->expects($this->at(5))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(11))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(14))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(15))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->any())->method('getElement')->will($this->returnValue(false));
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->session->expects($this->any())->method('getRatingData')->will($this->returnValue($ratingCodes));
        $this->session->expects($this->any())->method('setRatingData')->will($this->returnSelf());
        $this->block->toHtml();
    }

    public function testToHtmlCoreRegistryRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->will($this->returnValue($this->rating));
        $this->form->expects($this->at(5))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(11))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(14))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(15))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->any())->method('getElement')->will($this->returnValue(false));
        $this->session->expects($this->any())->method('getRatingData')->will($this->returnValue(false));
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->rating->expects($this->any())->method('getRatingCodes')->will($this->returnValue($ratingCodes));
        $this->block->toHtml();
    }

    public function testToHtmlWithoutRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->will($this->returnValue(false));
        $this->systemStore->expects($this->atLeastOnce())->method('getStoreCollection')
            ->will($this->returnValue(['0' => $this->store]));
        $this->formFactory->expects($this->any())->method('create')->will($this->returnValue($this->form));
        $this->viewFileSystem->expects($this->any())->method('getTemplateFileName')
            ->will($this->returnValue('template_file_name.html'));
        $this->fileSystem->expects($this->any())->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryReadInterface));
        $this->block->toHtml();
    }
}
