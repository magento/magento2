<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\User\Edit;

/**
 * User page left menu
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('User Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('User Info'),
                'title' => __('User Info'),
                'content' => $this->getLayout()->createBlock(\Magento\User\Block\User\Edit\Tab\Main::class)->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'roles_section',
            [
                'label' => __('User Role'),
                'title' => __('User Role'),
                'content' => $this->getLayout()->createBlock(
                    \Magento\User\Block\User\Edit\Tab\Roles::class,
                    'user.roles.grid'
                )->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
