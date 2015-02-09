<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * String translation model
 *
 * @method \Magento\Translation\Model\Resource\String _getResource()
 * @method \Magento\Translation\Model\Resource\String getResource()
 * @method int getStoreId()
 * @method \Magento\Translation\Model\String setStoreId(int $value)
 * @method string getTranslate()
 * @method \Magento\Translation\Model\String setTranslate(string $value)
 * @method string getLocale()
 * @method \Magento\Translation\Model\String setLocale(string $value)
 */
namespace Magento\Translation\Model;

class String extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Translation\Model\Resource\String');
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
