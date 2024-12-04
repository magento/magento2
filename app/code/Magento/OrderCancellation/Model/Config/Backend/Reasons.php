<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use Magento\Framework\Exception\LocalizedException;

class Reasons extends Serialized
{
    /**
     * Processing object before save data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);

            if (empty($value)) {
                throw new LocalizedException(
                    __('At least one reason value is required')
                );
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
