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
namespace Magento\Backup\Block\Adminhtml;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Adminhtml backup page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Backup extends \Magento\Backend\Block\Template
{
    /**
     * Block's template
     *
     * @var string
     */
    protected $_template = 'Magento_Backup::backup/list.phtml';

    /**
     * @return AbstractBlock|void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getToolbar()->addChild(
            'createSnapshotButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('System Backup'),
                'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT . "')",
                'class' => 'primary system-backup'
            )
        );
        $this->getToolbar()->addChild(
            'createMediaBackupButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Database and Media Backup'),
                'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_MEDIA . "')",
                'class' => 'primary database-media-backup'
            )
        );
        $this->getToolbar()->addChild(
            'createButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Database Backup'),
                'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_DB . "')",
                'class' => 'task primary database-backup'
            )
        );

        $this->addChild('dialogs', 'Magento\Backup\Block\Adminhtml\Dialogs');
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('backupsGrid');
    }

    /**
     * Generate html code for pop-up messages that will appear when user click on "Rollback" link
     *
     * @return string
     */
    public function getDialogsHtml()
    {
        return $this->getChildHtml('dialogs');
    }
}
