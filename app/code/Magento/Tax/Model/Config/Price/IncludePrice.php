<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Price;

class IncludePrice extends \Magento\Framework\App\Config\Value
{
    /**
     * @return void
     */
    public function afterSave()
    {
        parent::afterSave();
        $this->_cacheManager->clean(['checkout_quote']);
    }
}
