<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
