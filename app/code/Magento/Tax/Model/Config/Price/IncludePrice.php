<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Price;

/**
 * Class \Magento\Tax\Model\Config\Price\IncludePrice
 *
 * @since 2.0.0
 */
class IncludePrice extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        $result = parent::afterSave();
        $this->_cacheManager->clean(['checkout_quote']);

        return $result;
    }
}
