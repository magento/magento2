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
namespace Magento\Authorizenet\Model\Authorizenet;

/**
 * @method \Magento\Authorizenet\Model\Resource\Authorizenet\Debug _getResource()
 * @method \Magento\Authorizenet\Model\Resource\Authorizenet\Debug getResource()
 * @method string getRequestBody()
 * @method \Magento\Authorizenet\Model\Authorizenet\Debug setRequestBody(string $value)
 * @method string getResponseBody()
 * @method \Magento\Authorizenet\Model\Authorizenet\Debug setResponseBody(string $value)
 * @method string getRequestSerialized()
 * @method \Magento\Authorizenet\Model\Authorizenet\Debug setRequestSerialized(string $value)
 * @method string getResultSerialized()
 * @method \Magento\Authorizenet\Model\Authorizenet\Debug setResultSerialized(string $value)
 * @method string getRequestDump()
 * @method \Magento\Authorizenet\Model\Authorizenet\Debug setRequestDump(string $value)
 * @method string getResultDump()
 * @method \Magento\Authorizenet\Model\Authorizenet\Debug setResultDump(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Debug extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Authorizenet\Model\Resource\Authorizenet\Debug');
    }
}
