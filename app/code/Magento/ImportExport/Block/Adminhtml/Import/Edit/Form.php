<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Import edit form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Basic import model
     *
     * @var Import
     */
    protected $_importModel;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\EntityFactory
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory
     */
    protected $_behaviorFactory;

    /**
     * @var Import\ImageDirectoryBaseProvider
     */
    private $imagesDirectoryProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Source\Import\EntityFactory $entityFactory
     * @param \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory
     * @param array $data
     * @param Import\ImageDirectoryBaseProvider|null $imageDirProvider
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Source\Import\EntityFactory $entityFactory,
        \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory,
        array $data = [],
        ?Import\ImageDirectoryBaseProvider $imageDirProvider = null
    ) {
        $this->_entityFactory = $entityFactory;
        $this->_behaviorFactory = $behaviorFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_importModel = $importModel;
        $this->imagesDirectoryProvider = $imageDirProvider
            ?? ObjectManager::getInstance()->get(Import\ImageDirectoryBaseProvider::class);
    }

    /**
     * Add fieldsets
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('adminhtml/*/validate'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        // base fieldset
        $fieldsets['base'] = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Import Settings')]
        )->addField(
            'entity',
            'select',
            [
                'name' => 'entity',
                'title' => __('Entity Type'),
                'label' => __('Entity Type'),
                'required' => true,
                'onchange' => 'varienImport.handleEntityTypeSelector();',
                'values' => $this->_entityFactory->create()->toOptionArray(),
                'after_element_html' => $this->getDownloadSampleFileHtml(),
            ]
        );

        // add behaviour fieldsets
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        foreach ($uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $fieldset = $form->addFieldset(
                $behaviorCode . '_fieldset',
                ['legend' => __('Import Behavior'), 'class' => 'no-display']
            );
            $fieldset->addField(
                $behaviorCode,
                'select',
                [
                    'name' => 'behavior',
                    'title' => __('Import Behavior'),
                    'label' => __('Import Behavior'),
                    'required' => true,
                    'disabled' => true,
                    'values' => $this->_behaviorFactory->create($behaviorClass)->toOptionArray(),
                    'class' => $behaviorCode,
                    'onchange' => 'varienImport.handleImportBehaviorSelector();',
                    'note' => ' ',
                    'after_element_html' => $this->getImportBehaviorTooltip(),
                ]
            );
            $fieldset->addField(
                $behaviorCode . Import::FIELD_NAME_VALIDATION_STRATEGY,
                'select',
                [
                    'name' => Import::FIELD_NAME_VALIDATION_STRATEGY,
                    'title' => __('Validation Strategy'),
                    'label' => __('Validation Strategy'),
                    'required' => true,
                    'class' => $behaviorCode,
                    'disabled' => true,
                    'values' => [
                        ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR => __('Stop on Error'),
                        ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS => __('Skip error entries')
                    ],
                    'after_element_html' => $this->getDownloadSampleFileHtml(),
                ]
            );
            $fieldset->addField(
                $behaviorCode . '_' . Import::FIELD_NAME_ALLOWED_ERROR_COUNT,
                'text',
                [
                    'name' => Import::FIELD_NAME_ALLOWED_ERROR_COUNT,
                    'label' => __('Allowed Errors Count'),
                    'title' => __('Allowed Errors Count'),
                    'required' => true,
                    'disabled' => true,
                    'value' => 10,
                    'class' => $behaviorCode . ' validate-number validate-greater-than-zero input-text',
                    'note' => __(
                        'Please specify number of errors to halt import process'
                    ),
                ]
            );
            $fieldset->addField(
                $behaviorCode . '_' . Import::FIELD_FIELD_SEPARATOR,
                'text',
                [
                    'name' => Import::FIELD_FIELD_SEPARATOR,
                    'label' => __('Field separator'),
                    'title' => __('Field separator'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => ',',
                ]
            );
            $fieldset->addField(
                $behaviorCode . Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
                'text',
                [
                    'name' => Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
                    'label' => __('Multiple value separator'),
                    'title' => __('Multiple value separator'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                ]
            );
            $fieldset->addField(
                $behaviorCode . Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT,
                'text',
                [
                    'name' => Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT,
                    'label' => __('Empty attribute value constant'),
                    'title' => __('Empty attribute value constant'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT,
                ]
            );
            $fieldset->addField(
                $behaviorCode . Import::FIELDS_ENCLOSURE,
                'checkbox',
                [
                    'name' => Import::FIELDS_ENCLOSURE,
                    'label' => __('Fields enclosure'),
                    'title' => __('Fields enclosure'),
                    'value' => 1,
                ]
            );
            $fieldsets[$behaviorCode] = $fieldset;
        }

        // fieldset for file uploading
        $fieldset = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('File to Import'), 'class' => 'no-display']
        );
        $fieldset->addField(
            Import::FIELD_NAME_SOURCE_FILE,
            'file',
            [
                'name' => Import::FIELD_NAME_SOURCE_FILE,
                'label' => __('Select File to Import'),
                'title' => __('Select File to Import'),
                'required' => true,
                'class' => 'input-file',
                'note' => __(
                    'File must be saved in UTF-8 encoding for proper import'
                ),
            ]
        );
        $fieldset->addField(
            Import::FIELD_NAME_IMG_FILE_DIR,
            'text',
            [
                'name' => Import::FIELD_NAME_IMG_FILE_DIR,
                'label' => __('Images File Directory'),
                'title' => __('Images File Directory'),
                'required' => false,
                'class' => 'input-text',
                'note' => __(
                    $this->escapeHtml(
                        'For Type "Local Server" use relative path to <Magento installation>/'
                        .$this->imagesDirectoryProvider->getDirectoryRelativePath()
                        .', e.g. product_images, import_images/batch1'
                    )
                ),
            ]
        );
        $fieldsets['upload'] = $fieldset;

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get download sample file html
     *
     * @return string
     */
    protected function getDownloadSampleFileHtml()
    {
        $html = '<span id="sample-file-span" class="no-display"><a id="sample-file-link" href="#">'
            . __('Download Sample File')
            . '</a></span>';
        return $html;
    }

    /**
     * Get Import Behavior field tooltip
     *
     * @return string
     */
    private function getImportBehaviorTooltip()
    {
        $html = '<div class="admin__field-tooltip tooltip">
            <a class="admin__field-tooltip-action action-help" target="_blank" title="What is this?" 
                href="https://docs.magento.com/m2/ce/user_guide/system/data-import.html"><span>'
            . __('What is this?')
            . '</span></a></div>';
        return $html;
    }
}
