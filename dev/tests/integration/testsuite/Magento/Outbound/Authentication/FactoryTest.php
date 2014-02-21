<?php
/**
 * \Magento\Outbound\Authentication\Factory
 *
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
 * @copyright          Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license            http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Outbound\Authentication;

use Magento\Outbound\Authentication\Factory as AuthenticationFactory;
use Magento\Outbound\EndpointInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthenticationFactory */
    protected $_authFactory;

    protected function setUp()
    {
        $this->_authFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Outbound\Authentication\Factory', array(
                    'authenticationMap' => array(
                        EndpointInterface::AUTH_TYPE_HMAC => 'Magento\Outbound\Authentication\Hmac'
                    )
                ));
    }

    public function testGetFormatter()
    {
        $authObject = $this->_authFactory->getAuthentication(EndpointInterface::AUTH_TYPE_HMAC);
        $this->assertInstanceOf('Magento\Outbound\Authentication\Hmac', $authObject);
    }

    public function testGetFormatterIsCached()
    {
        $authObject = $this->_authFactory->getAuthentication(EndpointInterface::AUTH_TYPE_HMAC);
        $authObject2 = $this->_authFactory->getAuthentication(EndpointInterface::AUTH_TYPE_HMAC);
        $this->assertSame($authObject, $authObject2);
    }
}
