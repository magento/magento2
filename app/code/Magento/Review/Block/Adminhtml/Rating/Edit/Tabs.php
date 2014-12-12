<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Block\Adminhtml\Rating\Edit;

/**
 * Admin rating left menu
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rating_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rating Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            [
                'label' => __('Rating Information'),
                'title' => __('Rating Information'),
                'content' => $this->getLayout()
                        ->createBlock('Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form')
                        ->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
