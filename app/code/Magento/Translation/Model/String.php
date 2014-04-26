<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
