<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Model\Config\Source;

/**
 * Backups types' source model for system configuration
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     * @since 2.0.0
     */
    protected $_backupData = null;

    /**
     * @param \Magento\Backup\Helper\Data $backupData
     * @since 2.0.0
     */
    public function __construct(\Magento\Backup\Helper\Data $backupData)
    {
        $this->_backupData = $backupData;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $backupTypes = [];
        foreach ($this->_backupData->getBackupTypes() as $type => $label) {
            $backupTypes[] = ['label' => $label, 'value' => $type];
        }
        return $backupTypes;
    }
}
