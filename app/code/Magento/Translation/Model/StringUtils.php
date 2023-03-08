<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * String translation model
 *
 * @method int getStoreId()
 * @method StringUtils setStoreId(int $value)
 * @method string getTranslate()
 * @method StringUtils setTranslate(string $value)
 * @method string getLocale()
 * @method StringUtils setLocale(string $value)
 */
namespace Magento\Translation\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Translation\Model\ResourceModel\StringUtils as ResourceStringUtils;

class StringUtils extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceStringUtils::class);
    }

    /**
     * @param string $string
     * @return $this
     */
    public function setString($string)
    {
        $this->setData('string', $string);
        return $this;
    }

    /**
     * Retrieve string
     *
     * @return string
     */
    public function getString()
    {
        return $this->getData('string');
    }
}
