<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\StoreManagerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupFactory;

class GroupManagement implements \Magento\Customer\Api\GroupManagementInterface
{
    const XML_PATH_DEFAULT_ID = 'customer/create_account/default_group';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GroupFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GroupFactory $groupFactory,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadonly($groupId)
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group = $this->groupFactory->create();
        $group->load($groupId);
        if (is_null($group->getId())) {
            throw NoSuchEntityException::singleField('groupId', $groupId);
        }
        return $groupId > 0 && !$group->usesAsDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGroup($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getCode();
        }
        try {
            $groupId = $this->scopeConfig->getValue(
                self::XML_PATH_DEFAULT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Magento\Framework\App\InitException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        }
        try {
            return $this->groupRepository->get($groupId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::doubleField('groupId', $groupId, 'storeId', $storeId);
        }
    }
}
