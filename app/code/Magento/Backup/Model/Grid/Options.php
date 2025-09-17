<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backup\Model\Grid;

/**
 * Backup types option array
 *
 * @api
 * @since 100.0.2
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Backup\Helper\Data $backupHelper
     */
    public function __construct(\Magento\Backup\Helper\Data $backupHelper)
    {
        $this->_helper = $backupHelper;
    }

    /**
     * Return backup types array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_helper->getBackupTypes();
    }
}
