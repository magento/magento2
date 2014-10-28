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
namespace Magento\User\Helper;

/**
 * @magentoAppArea adminhtml
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        parent::setUp();
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\User\Helper\Data');
    }

    /**
     * Test generate unique token for reset password confirmation link
     *
     * @covers \Magento\User\Helper\Data::generateResetPasswordLinkToken
     */
    public function testGenerateResetPasswordLinkToken()
    {
        $actual = $this->_helper->generateResetPasswordLinkToken();
        $this->assertGreaterThan(15, strlen($actual));
    }

    /**
     * Test retrieve customer reset password link expiration period in days
     *
     */
    public function testGetResetPasswordLinkExpirationPeriod()
    {
        /** @var $configModel \Magento\Backend\App\ConfigInterface */
        $configModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\App\ConfigInterface'
        );
        $this->assertEquals(
            1,
            (int)$configModel->getValue(
                \Magento\User\Helper\Data::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD
            )
        );
    }
}
