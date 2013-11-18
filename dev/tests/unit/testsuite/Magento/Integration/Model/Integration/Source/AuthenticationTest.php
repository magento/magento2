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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Integration\Source;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    public function testToOptionArray()
    {
        /** @var \Magento\Integration\Model\Integration\Source\Authentication */
        $authSource = new \Magento\Integration\Model\Integration\Source\Authentication();
        /** @var array */
        $expectedAuthArr = array(
            \Magento\Integration\Model\Integration::AUTHENTICATION_OAUTH => __('OAuth'),
            \Magento\Integration\Model\Integration::AUTHENTICATION_MANUAL => __('Manual'),
        );
        $authArr = $authSource->toOptionArray();
        $this->assertEquals($expectedAuthArr, $authArr, "Authentication source arrays don't match");
    }
}
