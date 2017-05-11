<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

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
     * @var \Magento\ImportExport\Model\Import
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Source\Import\EntityFactory $entityFactory
     * @param \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Source\Import\EntityFactory $entityFactory,
        \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory,
        array $data = []
    ) {
        $this->_entityFactory = $entityFactory;
        $this->_behaviorFactory = $behaviorFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_importModel = $importModel;
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
        $fieldsets['base'] = $form->addFieldset('base_fieldset', ['legend' => __('Import Settings')]);
        $fieldsets['base']->addField(
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
            $fieldsets[$behaviorCode] = $form->addFieldset(
                $behaviorCode . '_fieldset',
                ['legend' => __('Import Behavior'), 'class' => 'no-display']
            );
            /** @var $behaviorSource \Magento\ImportExport\Model\Source\Import\AbstractBehavior */
            $fieldsets[$behaviorCode]->addField(
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
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . \Magento\ImportExport\Model\Import::FIELD_NAME_VALIDATION_STRATEGY,
                'select',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_VALIDATION_STRATEGY,
                    'title' => __(' '),
                    'label' => __(' '),
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
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . '_' . \Magento\ImportExport\Model\Import::FIELD_NAME_ALLOWED_ERROR_COUNT,
                'text',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_ALLOWED_ERROR_COUNT,
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
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . '_' . \Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR,
                'text',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR,
                    'label' => __('Field separator'),
                    'title' => __('Field separator'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => ',',
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . \Magento\ImportExport\Model\Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
                'text',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
                    'label' => __('Multiple value separator'),
                    'title' => __('Multiple value separator'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . \Magento\ImportExport\Model\Import::FIELDS_ENCLOSURE,
                'checkbox',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELDS_ENCLOSURE,
                    'label' => __('Fields enclosure'),
                    'title' => __('Fields enclosure'),
                    'value' => 1,
                ]
            );
        }

        // fieldset for file uploading
        $fieldsets['upload'] = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('File to Import'), 'class' => 'no-display']
        );
        $fieldsets['upload']->addField(
            \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE,
            'file',
            [
                'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE,
                'label' => __('Select File to Import'),
                'title' => __('Select File to Import'),
                'required' => true,
                'class' => 'input-file'
            ]
        );
        $fieldsets['upload']->addField(
            \Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR,
            'text',
            [
                'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR,
                'label' => __('Images File Directory'),
                'title' => __('Images File Directory'),
                'required' => false,
                'class' => 'input-text',
                'note' => __(
                    'For Type "Local Server" use relative path to Magento installation,
                                e.g. var/export, var/import, var/export/some/dir'
                ),
            ]
        );

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
}
