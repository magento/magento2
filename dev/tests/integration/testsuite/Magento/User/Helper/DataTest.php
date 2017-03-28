<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\User\Helper\Data::class
        );
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
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        );
        $this->assertEquals(
            2,
            (int)$configModel->getValue(
                \Magento\User\Helper\Data::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD
            )
        );
    }
}
