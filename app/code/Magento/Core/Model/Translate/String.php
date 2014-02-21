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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * String translation model
 *
 * @method \Magento\Core\Model\Resource\Translate\String _getResource()
 * @method \Magento\Core\Model\Resource\Translate\String getResource()
 * @method int getStoreId()
 * @method \Magento\Core\Model\Translate\String setStoreId(int $value)
 * @method string getTranslate()
 * @method \Magento\Core\Model\Translate\String setTranslate(string $value)
 * @method string getLocale()
 * @method \Magento\Core\Model\Translate\String setLocale(string $value)
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Translate;

class String extends \Magento\Core\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Translate\String');
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
