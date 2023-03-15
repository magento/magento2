<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\Config\Backend;

use Exception;
use Magento\Framework\App\Config\Value as ConfigValue;

class Priority extends ConfigValue
{
    /**
     * @return $this
     * @throws Exception
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value < 0 || $value > 1) {
            throw new Exception(__('The priority must be between 0 and 1.'));
        } elseif ($value == 0 && !($value === '0' || $value === '0.0')) {
            throw new Exception(__('The priority must be between 0 and 1.'));
        }
        return $this;
    }
}
