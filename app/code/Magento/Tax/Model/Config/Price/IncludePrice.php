<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Price;

use Magento\Framework\App\Config\Value;

class IncludePrice extends Value
{
    /**
     * @return $this
     */
    public function afterSave()
    {
        $result = parent::afterSave();
        $this->_cacheManager->clean(['checkout_quote']);

        return $result;
    }
}
