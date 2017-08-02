<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Block\Adminhtml;

use Magento\Backend\Block\Template;

/**
 * Backend rollback dialogs block
 * @since 2.0.0
 */
class Dialogs extends Template
{
    /**
     * Block's template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backup::backup/dialogs.phtml';
}
