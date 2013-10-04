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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * @method \Magento\GoogleCheckout\Model\Resource\Api\Debug _getResource()
 * @method \Magento\GoogleCheckout\Model\Resource\Api\Debug getResource()
 * @method string getDir()
 * @method \Magento\GoogleCheckout\Model\Api\Debug setDir(string $value)
 * @method string getUrl()
 * @method \Magento\GoogleCheckout\Model\Api\Debug setUrl(string $value)
 * @method string getRequestBody()
 * @method \Magento\GoogleCheckout\Model\Api\Debug setRequestBody(string $value)
 * @method string getResponseBody()
 * @method \Magento\GoogleCheckout\Model\Api\Debug setResponseBody(string $value)
 *
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleCheckout\Model\Api;

class Debug extends \Magento\Core\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magento\GoogleCheckout\Model\Resource\Api\Debug');
    }
}
