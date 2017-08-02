<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store;

/**
 * Store / store view / website delete form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Delete extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Class constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_objectId = 'item_id';
        $this->_mode = 'delete';
        $this->_blockGroup = 'Magento_Backend';
        $this->_controller = 'system_store';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');

        $this->buttonList->update('delete', 'region', 'toolbar');
        $this->buttonList->update('delete', 'onclick', null);
        $this->buttonList->update(
            'delete',
            'data_attribute',
            ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]
        );

        $this->buttonList->add(
            'cancel',
            ['label' => __('Cancel'), 'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')'],
            2,
            100,
            'toolbar'
        );
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __(
            "Delete %1 '%2'",
            $this->getStoreTypeTitle(),
            $this->escapeHtml($this->getChildBlock('form')->getDataObject()->getName())
        );
    }

    /**
     * Set store type title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setStoreTypeTitle($title)
    {
        $this->buttonList->update('delete', 'label', __('Delete %1', $title));
        return $this->setData('store_type_title', $title);
    }

    /**
     * Set back URL for "Cancel" and "Back" buttons
     *
     * @param string $url
     * @return $this
     * @since 2.0.0
     */
    public function setBackUrl($url)
    {
        $this->setData('back_url', $url);
        $this->buttonList->update('cancel', 'onclick', "setLocation('" . $url . "')");
        $this->buttonList->update('back', 'onclick', "setLocation('" . $url . "')");
        return $this;
    }
}
