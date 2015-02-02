<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store;

/**
 * Adminhtml store edit
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $this->_objectId = 'website_id';
                $saveLabel = __('Save Web Site');
                $deleteLabel = __('Delete Web Site');
                $deleteUrl = $this->getUrl(
                    '*/*/deleteWebsite',
                    ['item_id' => $this->_coreRegistry->registry('store_data')->getId()]
                );
                break;
            case 'group':
                $this->_objectId = 'group_id';
                $saveLabel = __('Save Store');
                $deleteLabel = __('Delete Store');
                $deleteUrl = $this->getUrl(
                    '*/*/deleteGroup',
                    ['item_id' => $this->_coreRegistry->registry('store_data')->getId()]
                );
                break;
            case 'store':
                $this->_objectId = 'store_id';
                $saveLabel = __('Save Store View');
                $deleteLabel = __('Delete Store View');
                $deleteUrl = $this->getUrl(
                    '*/*/deleteStore',
                    ['item_id' => $this->_coreRegistry->registry('store_data')->getId()]
                );
                break;
            default:
                $saveLabel = '';
                $deleteLabel = '';
                $deleteUrl = '';
        }
        $this->_blockGroup = 'Magento_Backend';
        $this->_controller = 'system_store';

        parent::_construct();

        $this->buttonList->update('save', 'label', $saveLabel);
        $this->buttonList->update('delete', 'label', $deleteLabel);
        $this->buttonList->update('delete', 'onclick', 'setLocation(\'' . $deleteUrl . '\');');

        if (!$this->_coreRegistry->registry('store_data')) {
            return;
        }

        if (!$this->_coreRegistry->registry('store_data')->isCanDelete()) {
            $this->buttonList->remove('delete');
        }
        if ($this->_coreRegistry->registry('store_data')->isReadOnly()) {
            $this->buttonList->remove('save');
            $this->buttonList->remove('reset');
        }
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $editLabel = __('Edit Web Site');
                $addLabel = __('New Web Site');
                break;
            case 'group':
                $editLabel = __('Edit Store');
                $addLabel = __('New Store');
                break;
            case 'store':
                $editLabel = __('Edit Store View');
                $addLabel = __('New Store View');
                break;
        }

        return $this->_coreRegistry->registry('store_action') == 'add' ? $addLabel : $editLabel;
    }

    /**
     * Build child form class form name based on value of store_type in registry
     *
     * @return string
     */
    protected function _buildFormClassName()
    {
        return parent::_buildFormClassName() . '\\' . ucwords($this->_coreRegistry->registry('store_type'));
    }
}
