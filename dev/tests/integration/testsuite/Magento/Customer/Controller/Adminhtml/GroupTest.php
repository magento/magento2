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
namespace Magento\Customer\Controller\Adminhtml;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Service\V1\Data\CustomerGroupBuilder;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;

/**
 * @magentoAppArea adminhtml
 */
class GroupTest extends \Magento\Backend\Utility\Controller
{
    const TAX_CLASS_ID = 3;
    const CUSTOMER_GROUP_CODE = 'custom_group';
    const BASE_CONTROLLER_URL = 'http://localhost/index.php/backend/customer/group/';
    const CUSTOMER_GROUP_ID = 2;

    /** @var  \Magento\Framework\Session\SessionManagerInterface */
    private $session;

    public function setUp()
    {
        parent::setUp();
        $this->session = Bootstrap::getObjectManager()->get('Magento\Framework\Session\SessionManagerInterface');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->session->unsCustomerGroupData();
    }

    public function testNewActionNoCustomerGroupDataInSession()
    {
        $this->dispatch('backend/customer/group/new');
        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="title">\s*New Customer Group\s*<\/h1>/', $responseBody);
        $expected = '<input id="customer_group_code" name="code"  '
            . 'data-ui-id="group-form-fieldset-element-text-code"  value=""';
        $this->assertContains($expected, $responseBody);
    }

    public function testNewActionWithCustomerGroupDataInSession()
    {
        $customerGroupBuilder = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\Data\CustomerGroupBuilder');
        $customerGroupBuilder->setCode(self::CUSTOMER_GROUP_CODE);
        $customerGroupBuilder->setTaxClassId(self::TAX_CLASS_ID);
        $customerGroup = $customerGroupBuilder->create();
        $customerGroupData = $customerGroup->__toArray();
        if (array_key_exists('code', $customerGroupData)) {
            $customerGroupData['customer_group_code'] = $customerGroupData['code'];
            unset($customerGroupData['code']);
        }
        $this->session->setCustomerGroupData($customerGroupData);
        $this->dispatch('backend/customer/group/new');
        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="title">\s*New Customer Group\s*<\/h1>/', $responseBody);
        $expected = '<input id="customer_group_code" name="code"  '
            . 'data-ui-id="group-form-fieldset-element-text-code"  value="' . self::CUSTOMER_GROUP_CODE . '"';
        $this->assertContains($expected, $responseBody);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testDeleteActionNoGroupId()
    {
        $this->dispatch('backend/customer/group/delete');
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testDeleteActionExistingGroup()
    {
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $this->getRequest()->setParam('id', $groupId);
        $this->dispatch('backend/customer/group/delete');

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['The customer group has been deleted.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL . 'index'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testDeleteActionNonExistingGroupId()
    {
        $this->getRequest()->setParam('id', 10000);
        $this->dispatch('backend/customer/group/delete');

        /**
         * Check that error message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['The customer group no longer exists.']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testSaveActionExistingGroup()
    {
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);
        $this->getRequest()->setParam('id', $groupId);
        $this->getRequest()->setParam('code', self::CUSTOMER_GROUP_CODE);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages($this->logicalNot($this->isEmpty()), MessageInterface::TYPE_SUCCESS);

        $this->assertSessionMessages(
            $this->equalTo(['The customer group has been saved.']),
            MessageInterface::TYPE_SUCCESS
        );

        /** @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService */
        $groupService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $customerGroupData = \Magento\Framework\Service\SimpleDataObjectConverter::toFlatArray(
            $groupService->getGroup($groupId)
        );
        ksort($customerGroupData);

        $this->assertEquals(
            [
                'code' => self::CUSTOMER_GROUP_CODE,
                'id' => $groupId,
                'tax_class_id' => self::TAX_CLASS_ID
            ],
            $customerGroupData
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testSaveActionExistingGroupWithEmptyGroupCode()
    {
        /** @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService */
        $groupService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $originalCode = $groupService->getGroup($groupId)->getCode();

        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);
        $this->getRequest()->setParam('id', $groupId);
        $this->getRequest()->setParam('code', '');

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages(
            $this->equalTo(['Invalid value of "" provided for the code field.']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertEquals($originalCode, $groupService->getGroup($groupId)->getCode());
    }

    public function testSaveActionForwardNewCreateNewGroup()
    {
        $this->dispatch('backend/customer/group/save');
        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="title">\s*New Customer Group\s*<\/h1>/', $responseBody);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testSaveActionForwardNewEditExistingGroup()
    {
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $this->getRequest()->setParam('id', $groupId);
        $this->dispatch('backend/customer/group/save');

        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="title">\s*' . self::CUSTOMER_GROUP_CODE . '\s*<\/h1>/', $responseBody);
    }

    public function testSaveActionNonExistingGroupId()
    {
        $this->getRequest()->setParam('id', 10000);
        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertSessionMessages($this->logicalNot($this->isEmpty()), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with groupId = 10000']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL . 'edit/'));
        $this->assertEquals(null, $this->session->getCustomerGroupData());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testSaveActionNewGroupWithExistingGroupCode()
    {
        /** @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService */
        $groupService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');

        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $originalCode = $groupService->getGroup($groupId)->getCode();

        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);
        $this->getRequest()->setParam('code', self::CUSTOMER_GROUP_CODE);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages($this->equalTo(['Customer Group already exists.']), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertEquals($originalCode, $groupService->getGroup($groupId)->getCode());
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL . 'edit/'));
        $this->assertEquals(self::CUSTOMER_GROUP_CODE, $this->session->getCustomerGroupData()['customer_group_code']);
        $this->assertEquals(self::TAX_CLASS_ID, $this->session->getCustomerGroupData()['tax_class_id']);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testSaveActionNewGroupWithoutGroupCode()
    {
        /** @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService */
        $groupService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $originalCode = $groupService->getGroup($groupId)->getCode();

        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages(
            $this->equalTo(['Invalid value of "" provided for the code field.']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertEquals($originalCode, $groupService->getGroup($groupId)->getCode());
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL . 'edit/'));
        $this->assertEquals('', $this->session->getCustomerGroupData()['customer_group_code']);
        $this->assertEquals(self::TAX_CLASS_ID, $this->session->getCustomerGroupData()['tax_class_id']);
    }

    /**
     * Find the group with a given code.
     *
     * @param string $code
     * @return int
     */
    protected function findGroupIdWithCode($code)
    {
        $groupService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerGroupService');
        foreach ($groupService->getGroups() as $group) {
            if ($group->getCode() === $code) {
                return $group->getId();
            }
        }

        return -1;
    }
}
