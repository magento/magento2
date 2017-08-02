<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Block\Adminhtml;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Adminhtml backup page content block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Backup extends \Magento\Backend\Block\Template
{
    /**
     * Block's template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backup::backup/list.phtml';

    /**
     * @return AbstractBlock|void
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getToolbar()->addChild(
            'createSnapshotButton',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('System Backup'),
                'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT . "')",
                'class' => 'primary system-backup'
            ]
        );
        $this->getToolbar()->addChild(
            'createMediaBackupButton',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Database and Media Backup'),
                'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_MEDIA . "')",
                'class' => 'primary database-media-backup'
            ]
        );
        $this->getToolbar()->addChild(
            'createButton',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Database Backup'),
                'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_DB . "')",
                'class' => 'task primary database-backup'
            ]
        );

        $this->addChild('dialogs', \Magento\Backup\Block\Adminhtml\Dialogs::class);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('backupsGrid');
    }

    /**
     * Generate html code for pop-up messages that will appear when user click on "Rollback" link
     *
     * @return string
     * @since 2.0.0
     */
    public function getDialogsHtml()
    {
        return $this->getChildHtml('dialogs');
    }
}
