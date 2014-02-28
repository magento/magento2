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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer group edit block
 */
namespace Magento\Customer\Block\Adminhtml\Group;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_groupService = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_groupService = $groupService;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_group';
        $this->_blockGroup = 'Magento_Customer';

        $this->_updateButton('save', 'label', __('Save Customer Group'));
        $this->_updateButton('delete', 'label', __('Delete Customer Group'));

        /** @var \Magento\Customer\Service\V1\Dto\CustomerGroup $group */
        $group = $this->_coreRegistry->registry('current_group');
        if (!$group || !$group->getId() || !$this->_groupService->canDelete($group->getId())) {
            $this->_removeButton('delete');
        }
    }

    public function getHeaderText()
    {
        $currentGroup = $this->_coreRegistry->registry('current_group');
        if (!is_null($currentGroup->getId())) {
            return __('Edit Customer Group "%1"', $this->escapeHtml($currentGroup->getCustomerGroupCode()));
        } else {
            return __('New Customer Group');
        }
    }

    public function getHeaderCssClass()
    {
        return 'icon-head head-customer-groups';
    }
}
