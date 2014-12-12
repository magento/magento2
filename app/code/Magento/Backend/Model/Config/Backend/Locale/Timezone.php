<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * System config email field backend model
 */
namespace Magento\Backend\Model\Config\Backend\Locale;

use Magento\Framework\Model\Exception;

class Timezone extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     * @throws Exception
     */
    public function beforeSave()
    {
        if (!in_array($this->getValue(), \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC))) {
            throw new Exception(__('Please correct the timezone.'));
        }
        return $this;
    }
}
