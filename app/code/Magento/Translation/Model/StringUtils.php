<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * String translation model
 *
 * @method \Magento\Translation\Model\ResourceModel\StringUtils _getResource()
 * @method \Magento\Translation\Model\ResourceModel\StringUtils getResource()
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
 * @since 2.0.0
 */
class StringUtils extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Translation\Model\ResourceModel\StringUtils::class);
    }

    /**
     * @param string $string
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getString()
    {
        return $this->getData('string');
    }
}
