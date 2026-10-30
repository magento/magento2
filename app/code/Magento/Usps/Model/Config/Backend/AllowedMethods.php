<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 *
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;

/**
 * Backend model for USPS allowed methods fields
 *
 * Preserves the existing value when the field is hidden due to usps_type dependency.
 * This prevents the value from being cleared when switching between API types.
 */
class AllowedMethods extends Value
{
    private const USPS_TYPE_PATH = 'carriers/usps/usps_type';
    private const USPS_TYPE_XML = 'USPS_XML';
    private const USPS_TYPE_REST = 'USPS_REST';

    /**
     * The USPS type this backend model applies to (to be set by child classes)
     *
     * @var string
     */
    protected string $applicableUspsType = '';

    /**
     * Process value before saving
     *
     * If the current usps_type doesn't match this field's applicable type,
     * preserve the existing database value instead of saving empty.
     *
     * @return $this
     */
    public function beforeSave()
    {
        $currentUspsType = $this->getCurrentUspsType();

        // If field is not applicable for current usps_type, preserve old value
        if ($this->applicableUspsType && $currentUspsType !== $this->applicableUspsType) {
            $oldValue = $this->getOldValue();
            if ($oldValue !== null) {
                $this->setValue($oldValue);
            }
        }

        return parent::beforeSave();
    }

    /**
     * Get the current usps_type being saved
     *
     * @return string
     */
    private function getCurrentUspsType(): string
    {
        // Try to get from the current save request (fieldset data)
        $fieldsetData = $this->getData('fieldset_data');
        if (is_array($fieldsetData) && isset($fieldsetData['usps_type'])) {
            return (string)$fieldsetData['usps_type'];
        }

        // Fall back to reading from config
        return (string)$this->_config->getValue(
            self::USPS_TYPE_PATH,
            $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeId()
        );
    }
}
