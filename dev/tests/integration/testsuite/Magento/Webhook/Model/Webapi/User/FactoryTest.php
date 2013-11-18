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
 * @package     Magento_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Webapi\User;

/**
 * \Magento\Webhook\Model\Webapi\User\Factory
 *
 * @magentoDbIsolation enabled
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** Values being sent to user service */
    const VALUE_COMPANY_NAME = 'company name';
    const VALUE_SECRET_VALUE = 'secret_value';
    const VALUE_KEY_VALUE = 'key_value';
    const VALUE_EMAIL = 'email@example.com';

    /** @var  array */
    private $_userContext;

    /** @var  int */
    private $_apiUserId;

    protected function setUp()
    {
        $this->_userContext = array(
            'email'     => self::VALUE_EMAIL,
            'key'       => self::VALUE_KEY_VALUE,
            'secret'    => self::VALUE_SECRET_VALUE,
            'company'   => self::VALUE_COMPANY_NAME
        );
    }

    protected function tearDown()
    {
        /** @var \Magento\Webapi\Model\Acl\User $user */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\User');
        $user->load($this->_apiUserId);
        $user->delete();
    }

    public function testCreate()
    {
        /** @var \Magento\Webhook\Model\Webapi\User\Factory $userFactory */
        $userFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Webapi\User\Factory');
        $this->_apiUserId = $userFactory->createUser($this->_userContext, array('webhook/create'));

        /** @var \Magento\Webapi\Model\Acl\User $user */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\User');
        $user->load($this->_apiUserId);

        $this->assertEquals(self::VALUE_COMPANY_NAME, $user->getCompanyName());
        $this->assertEquals(self::VALUE_EMAIL, $user->getContactEmail());
        $this->assertEquals(self::VALUE_SECRET_VALUE, $user->getSecret());
        $this->assertEquals(self::VALUE_KEY_VALUE, $user->getApiKey());
        $this->assertNotEquals(0, $user->getRoleId());

        /** @var \Magento\Webapi\Model\Resource\Acl\Rule $ruleResources */
        $ruleResources = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Resource\Acl\Rule');
        $rules = $ruleResources->getResourceIdsByRole($user->getRoleId());
        $this->assertNotEmpty($rules);
    }

}
