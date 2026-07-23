<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model\Config\Source;

/**
 * Backups types' source model for system configuration
 *
 * @api
 * @since 100.0.2
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backupData = null;

    /**
     * @param \Magento\Backup\Helper\Data $backupData
     */
    public function __construct(\Magento\Backup\Helper\Data $backupData)
    {
        $this->_backupData = $backupData;
    }

    /**
     * @inheritDoc
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
