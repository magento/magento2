<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\System\Store;

/**
 * Store / store view / website delete form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Delete extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Class constructor
     *
     * @return void
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

        $this->buttonList->update('delete', 'region', 'footer');
        $this->buttonList->update('delete', 'onclick', null);
        $this->buttonList->update(
            'delete',
            'data_attribute',
            array('mage-init' => array('button' => array('event' => 'save', 'target' => '#edit_form')))
        );

        $this->buttonList->add(
            'cancel',
            array('label' => __('Cancel'), 'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')'),
            2,
            100,
            'footer'
        );
    }

    /**
     * Get edit form container header text
     *
     * @return string
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
     */
    public function setBackUrl($url)
    {
        $this->setData('back_url', $url);
        $this->buttonList->update('cancel', 'onclick', "setLocation('" . $url . "')");
        $this->buttonList->update('back', 'onclick', "setLocation('" . $url . "')");
        return $this;
    }
}
