<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup types option array
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Model\Grid;

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
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_helper->getBackupTypes();
    }
}
