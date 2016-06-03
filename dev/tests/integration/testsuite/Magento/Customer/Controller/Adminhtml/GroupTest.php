<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class GroupTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    const TAX_CLASS_ID = 3;
    const TAX_CLASS_NAME = 'Retail Customer';
    const CUSTOMER_GROUP_CODE = 'custom_group';
    const BASE_CONTROLLER_URL = 'http://localhost/index.php/backend/customer/group/';
    const CUSTOMER_GROUP_ID = 2;

    /** @var  \Magento\Framework\Session\SessionManagerInterface */
    private $session;

    /** @var  \Magento\Customer\Api\GroupRepositoryInterface */
    private $groupRepository;

    public function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->session = $objectManager->get('Magento\Framework\Session\SessionManagerInterface');
        $this->groupRepository = $objectManager->get('Magento\Customer\Api\GroupRepositoryInterface');
    }

    public function tearDown()
    {
        parent::tearDown();
        //$this->session->unsCustomerGroupData();
    }

    public function testNewActionNoCustomerGroupDataInSession()
    {
        $this->dispatch('backend/customer/group/new');
        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="page-title">\s*New Customer Group\s*<\/h1>/', $responseBody);
        $expected = '<input id="customer_group_code" name="code"  '
            . 'data-ui-id="group-form-fieldset-element-text-code"  value=""';
        $this->assertContains($expected, $responseBody);
    }

    public function testNewActionWithCustomerGroupDataInSession()
    {
        /** @var \Magento\Customer\Api\Data\GroupInterfaceFactory $customerGroupFactory */
        $customerGroupFactory = $this->_objectManager
            ->get('Magento\Customer\Api\Data\GroupInterfaceFactory');
        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        $customerGroup = $customerGroupFactory->create()
            ->setCode(self::CUSTOMER_GROUP_CODE)
            ->setTaxClassId(self::TAX_CLASS_ID);
        /** @var \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor */
        $dataObjectProcessor = $this->_objectManager->get('Magento\Framework\Reflection\DataObjectProcessor');
        $customerGroupData = $dataObjectProcessor
            ->buildOutputDataArray($customerGroup, 'Magento\Customer\Api\Data\GroupInterface');
        if (array_key_exists('code', $customerGroupData)) {
            $customerGroupData['customer_group_code'] = $customerGroupData['code'];
            unset($customerGroupData['code']);
        }
        $this->session->setCustomerGroupData($customerGroupData);
        $this->dispatch('backend/customer/group/new');
        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="page-title">\s*New Customer Group\s*<\/h1>/', $responseBody);
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
            $this->equalTo(['You deleted the customer group.']),
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
            $this->equalTo(['You saved the customer group.']),
            MessageInterface::TYPE_SUCCESS
        );

        /** @var \Magento\Framework\Api\SimpleDataObjectConverter $simpleDataObjectConverter */
        $simpleDataObjectConverter = Bootstrap::getObjectManager()
            ->get('Magento\Framework\Api\SimpleDataObjectConverter');
        $customerGroupData = $simpleDataObjectConverter->toFlatArray(
            $this->groupRepository->getById($groupId),
            'Magento\Customer\Api\Data\GroupInterface'
        );
        ksort($customerGroupData);

        $this->assertEquals(
            [
                'code' => self::CUSTOMER_GROUP_CODE,
                'id' => $groupId,
                'tax_class_id' => self::TAX_CLASS_ID,
                'tax_class_name' => self::TAX_CLASS_NAME,
            ],
            $customerGroupData
        );
    }

    public function testSaveActionCreateNewGroupWithoutCode()
    {
        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages(
            $this->equalTo(['code is a required field.']),
            MessageInterface::TYPE_ERROR
        );
    }

    public function testSaveActionForwardNewCreateNewGroup()
    {
        $this->dispatch('backend/customer/group/save');
        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<h1 class\="page-title">\s*New Customer Group\s*<\/h1>/', $responseBody);
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
        $this->assertRegExp('/<h1 class\="page-title">\s*' . self::CUSTOMER_GROUP_CODE . '\s*<\/h1>/', $responseBody);
    }

    public function testSaveActionNonExistingGroupId()
    {
        $this->getRequest()->setParam('id', 10000);
        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertSessionMessages($this->logicalNot($this->isEmpty()), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with id = 10000']),
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
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $originalCode = $this->groupRepository->getById($groupId)->getCode();

        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);
        $this->getRequest()->setParam('code', self::CUSTOMER_GROUP_CODE);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages($this->equalTo(['Customer Group already exists.']), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertEquals($originalCode, $this->groupRepository->getById($groupId)->getCode());
        $this->assertRedirect($this->stringStartsWith(self::BASE_CONTROLLER_URL . 'edit/'));
        $this->assertEquals(self::CUSTOMER_GROUP_CODE, $this->session->getCustomerGroupData()['customer_group_code']);
        $this->assertEquals(self::TAX_CLASS_ID, $this->session->getCustomerGroupData()['tax_class_id']);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testSaveActionNewGroupWithoutGroupCode()
    {
        $groupId = $this->findGroupIdWithCode(self::CUSTOMER_GROUP_CODE);
        $originalCode = $this->groupRepository->getById($groupId)->getCode();

        $this->getRequest()->setParam('tax_class', self::TAX_CLASS_ID);

        $this->dispatch('backend/customer/group/save');

        $this->assertSessionMessages(
            $this->equalTo(['code is a required field.']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertEquals($originalCode, $this->groupRepository->getById($groupId)->getCode());
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
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->_objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        foreach ($this->groupRepository->getList($searchBuilder->create())->getItems() as $group) {
            if ($group->getCode() === $code) {
                return $group->getId();
            }
        }

        return -1;
    }
}
