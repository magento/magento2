<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * String translation model
 *
 * @method int getStoreId()
 * @method \Magento\Translation\Model\StringUtils setStoreId(int $value)
 * @method string getTranslate()
 * @method \Magento\Translation\Model\StringUtils setTranslate(string $value)
 * @method string getLocale()
 * @method \Magento\Translation\Model\StringUtils setLocale(string $value)
 */
namespace Magento\Translation\Model;

/**
 * Class \Magento\Translation\Model\StringUtils
 *
 */
class StringUtils extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Translation\Model\ResourceModel\StringUtils::class);
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
