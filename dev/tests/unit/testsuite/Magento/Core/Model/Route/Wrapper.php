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
 * Wrapper to pass method calls and arguments to mockup inside it
 */
namespace Magento\Core\Model\Route;

class Wrapper extends \PHPUnit_Framework_TestCase implements \Magento\Framework\Config\CacheInterface
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mock;

    public function __construct()
    {
        $this->_mock = $this->getMock('SomeClass', array('get', 'put'));
    }

    public function getRealMock()
    {
        return $this->_mock;
    }

    public function get($areaCode, $cacheId)
    {
        return $this->_mock->get($areaCode, $cacheId);
    }

    public function put($routes, $areaCode, $cacheId)
    {
        return $this->_mock->put($routes, $areaCode, $cacheId);
    }
}
