<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Adminhtml\Group;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Magento\Customer\Block\Adminhtml\Group\Edit
 *
 * @magentoAppArea adminhtml
 */
class EditTest extends AbstractController
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Execute per test initialization.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->layout = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class);
        $this->groupRepository = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
        $this->groupManagement = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\GroupManagementInterface::class
        );
        $this->registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
    }

    /**
     * Execute per test cleanup.
     */
    protected function tearDown(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_GROUP_ID);
    }

    /**
     * Verify that the Delete button does not exist for the default group.
     * @magentoAppIsolation enabled
     */
    public function testDeleteButtonNotExistInDefaultGroup()
    {
        $groupId = $this->groupManagement->getDefaultGroup(0)->getId();
        $this->registry->register(RegistryConstants::CURRENT_GROUP_ID, $groupId);
        $this->getRequest()->setParam('id', $groupId);

        /** @var $block Edit */
        $block = $this->layout->createBlock(\Magento\Customer\Block\Adminhtml\Group\Edit::class, 'block');
        $buttonsHtml = $block->getButtonsHtml();

        $this->assertStringNotContainsString('delete', $buttonsHtml);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testDeleteButtonExistInCustomGroup()
    {
        $builder = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\FilterBuilder::class);
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->addFilters([$builder->setField('code')->setValue('custom_group')->create()])->create();
        $customerGroup = $this->groupRepository->getList($searchCriteria)->getItems()[0];
        $this->getRequest()->setParam('id', $customerGroup->getId());
        $this->registry->register(RegistryConstants::CURRENT_GROUP_ID, $customerGroup->getId());

        /** @var $block Edit */
        $block = $this->layout->createBlock(\Magento\Customer\Block\Adminhtml\Group\Edit::class, 'block');
        $buttonsHtml = $block->getButtonsHtml();

        $this->assertStringContainsString('delete', $buttonsHtml);
    }
}
