<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Block\Adminhtml;

use Magento\Backend\Block\Template;

/**
 * Backend rollback dialogs block
 */
class Dialogs extends Template
{
    /**
     * Block's template
     *
     * @var string
     */
    protected $_template = 'Magento_Backup::backup/dialogs.phtml';
}
