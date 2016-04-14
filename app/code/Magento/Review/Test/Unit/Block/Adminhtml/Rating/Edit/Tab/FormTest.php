<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block\Adminhtml\Rating\Edit\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->ratingOptionCollection = $this->getMock(
            '\Magento\Review\Model\ResourceModel\Rating\Option\Collection',
            [],
            [],
            '',
            false
        );
        $this->element = $this->getMock(
            '\Magento\Framework\Data\Form\Element\Text',
            ['setValue', 'setIsChecked'],
            [],
            '',
            false
        );
        $this->session = $this->getMock(
            '\Magento\Framework\Session\Generic',
            ['getRatingData', 'setRatingData'],
            [],
            '',
            false
        );
        $this->rating = $this->getMock('\Magento\Review\Model\Rating', ['getId', 'getRatingCodes'], [], '', false);
        $this->optionRating = $this->getMock('\Magento\Review\Model\Rating\Option', [], [], '', false);
        $this->store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->form = $this->getMock('\Magento\Framework\Data\Form', [], [], '', false);
        $this->directoryReadInterface = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->registry = $this->getMock('\Magento\Framework\Registry');
        $this->formFactory = $this->getMock('\Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->optionFactory = $this->getMock('\Magento\Review\Model\Rating\OptionFactory', ['create'], [], '', false);
        $this->systemStore = $this->getMock('\Magento\Store\Model\System\Store', [], [], '', false);
        $this->viewFileSystem = $this->getMock('\Magento\Framework\View\FileSystem', [], [], '', false);
        $this->fileSystem = $this->getMock('\Magento\Framework\Filesystem', ['getDirectoryRead'], [], '', false);

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
            'Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form',
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
        $this->form->expects($this->at(7))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(13))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(16))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(17))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->any())->method('getElement')->will($this->returnValue(false));
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->session->expects($this->any())->method('getRatingData')->will($this->returnValue($ratingCodes));
        $this->session->expects($this->any())->method('setRatingData')->will($this->returnSelf());
        $this->block->toHtml();
    }

    public function testToHtmlCoreRegistryRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->will($this->returnValue($this->rating));
        $this->form->expects($this->at(7))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(13))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(16))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->at(17))->method('getElement')->will($this->returnValue($this->element));
        $this->form->expects($this->any())->method('getElement')->will($this->returnValue(false));
        $this->session->expects($this->any())->method('getRatingData')->will($this->returnValue(false));
        $ratingCodes = ['rating_codes' => ['0' => 'rating_code']];
        $this->rating->expects($this->any())->method('getRatingCodes')->will($this->returnValue($ratingCodes));
        $this->block->toHtml();
    }

    public function testToHtmlWithoutRatingData()
    {
        $this->registry->expects($this->any())->method('registry')->will($this->returnValue(false));
        $this->systemStore->expects($this->any())->method('getStoreCollection')
            ->will($this->returnValue(['0' => $this->store]));
        $this->formFactory->expects($this->any())->method('create')->will($this->returnValue($this->form));
        $this->viewFileSystem->expects($this->any())->method('getTemplateFileName')
            ->will($this->returnValue('template_file_name.html'));
        $this->fileSystem->expects($this->any())->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryReadInterface));
        $this->block->toHtml();
    }
}
