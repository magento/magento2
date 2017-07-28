<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Grid column block that is displayed only if rollback allowed
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Block\Adminhtml\Grid\Column;

/**
 * @api
 * @since 2.0.0
 */
class Rollback extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Magento\Backup\Helper\Data
     * @since 2.0.0
     */
    protected $_backupHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backup\Helper\Data $backupHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backup\Helper\Data $backupHelper,
        array $data = []
    ) {
        $this->_backupHelper = $backupHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check permission for rollback
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisplayed()
    {
        return $this->_backupHelper->isRollbackAllowed();
    }
}
