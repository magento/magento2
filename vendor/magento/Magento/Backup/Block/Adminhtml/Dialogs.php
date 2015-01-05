<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
