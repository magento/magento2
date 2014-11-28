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
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupDataBuilder;

class Save extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupDataBuilder $groupDataBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        GroupRepositoryInterface $groupRepository,
        GroupDataBuilder $groupDataBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct($context, $coreRegistry, $groupRepository, $groupDataBuilder);
    }

    /**
     * Store Customer Group Data to session
     *
     * @param array $customerGroupData
     * @return void
     */
    protected function storeCustomerGroupDataToSession($customerGroupData)
    {
        if (array_key_exists('code', $customerGroupData)) {
            $customerGroupData['customer_group_code'] = $customerGroupData['code'];
            unset($customerGroupData['code']);
        }
        $this->_getSession()->setCustomerGroupData($customerGroupData);
    }

    /**
     * Create or save customer group.
     *
     * @return void
     */
    public function execute()
    {
        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        $customerGroup = null;
        if ($taxClass) {
            $id = $this->getRequest()->getParam('id');
            try {
                if (!is_null($id)) {
                    $this->groupDataBuilder->populate($this->groupRepository->getById((int)$id));
                }
                $customerGroupCode = (string)$this->getRequest()->getParam('code');
                if (empty($customerGroupCode)) {
                    $customerGroupCode = null;
                }
                $this->groupDataBuilder->setCode($customerGroupCode);
                $this->groupDataBuilder->setTaxClassId($taxClass);
                $customerGroup = $this->groupDataBuilder->create();

                $this->groupRepository->save($customerGroup);

                $this->messageManager->addSuccess(__('The customer group has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl('customer/group'));
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                if ($customerGroup != null) {
                    $this->storeCustomerGroupDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $customerGroup,
                            '\Magento\Customer\Api\Data\GroupInterface'
                        )
                    );
                }
                $this->getResponse()->setRedirect($this->getUrl('customer/group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }
    }
}
