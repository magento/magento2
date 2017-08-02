<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Block\Adminhtml\Form\Field;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * HTML select element block with customer groups options
 * @since 2.0.0
 */
class Customergroup extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Customer groups cache
     *
     * @var array
     * @since 2.0.0
     */
    private $_customerGroups;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_addGroupAllOption = true;

    /**
     * @var GroupManagementInterface
     * @since 2.0.0
     */
    protected $groupManagement;

    /**
     * @var GroupRepositoryInterface
     * @since 2.0.0
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        GroupManagementInterface $groupManagement,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->groupManagement = $groupManagement;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId return name by customer group id
     * @return array|string
     * @since 2.0.0
     */
    protected function _getCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            $this->_customerGroups = [];
            foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $item) {
                $this->_customerGroups[$item->getId()] = $item->getCode();
            }
            $notLoggedInGroup = $this->groupManagement->getNotLoggedInGroup();
            $this->_customerGroups[$notLoggedInGroup->getId()] = $notLoggedInGroup->getCode();
        }
        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : null;
        }
        return $this->_customerGroups;
    }

    /**
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            if ($this->_addGroupAllOption) {
                $this->addOption(
                    $this->groupManagement->getAllCustomersGroup()->getId(),
                    __('ALL GROUPS')
                );
            }
            foreach ($this->_getCustomerGroups() as $groupId => $groupLabel) {
                $this->addOption($groupId, addslashes($groupLabel));
            }
        }
        return parent::_toHtml();
    }
}
