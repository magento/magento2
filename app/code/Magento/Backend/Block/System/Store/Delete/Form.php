<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Delete;

use Magento\Backup\Helper\Data as BackupHelper;
use Magento\Framework\App\ObjectManager;

/**
 * Adminhtml cms block edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var BackupHelper
     */
    private $backup;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [],
        ?BackupHelper $backup = null
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->backup = $backup ?? ObjectManager::getInstance()->get(BackupHelper::class);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('store_delete_form');
        $this->setTitle(__('Block Information'));
    }

    /**
     * @inheritDoc
     */
    protected function _prepareForm()
    {
        $dataObject = $this->getDataObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('store_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Backup Options'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('item_id', 'hidden', ['name' => 'item_id', 'value' => $dataObject->getId()]);

        $backupOptions = ['0' => __('No')];
        $backupSelected = '0';
        if ($this->backup->isEnabled()) {
            $backupOptions['1'] = __('Yes');
            $backupSelected = '1';
        }
        $fieldset->addField(
            'create_backup',
            'select',
            [
                'label' => __('Create DB Backup'),
                'title' => __('Create DB Backup'),
                'name' => 'create_backup',
                'options' => $backupOptions,
                'value' => $backupSelected
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
