<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup types option array
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Model\Grid;

/**
 * @api
 * @since 2.0.0
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Backup\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @param \Magento\Backup\Helper\Data $backupHelper
     * @since 2.0.0
     */
    public function __construct(\Magento\Backup\Helper\Data $backupHelper)
    {
        $this->_helper = $backupHelper;
    }

    /**
     * Return backup types array
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_helper->getBackupTypes();
    }
}
