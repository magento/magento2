<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;

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
     * @param GroupInterfaceFactory $groupDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct($context, $coreRegistry, $groupRepository, $groupDataFactory);
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
                    $customerGroup = $this->groupRepository->getById((int)$id);
                } else {
                    $customerGroup = $this->groupDataFactory->create();
                }
                $customerGroupCode = (string)$this->getRequest()->getParam('code');
                if (empty($customerGroupCode)) {
                    $customerGroupCode = null;
                }
                $customerGroup->setCode($customerGroupCode);
                $customerGroup->setTaxClassId($taxClass);

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
                $this->getResponse()->setRedirect($this->getUrl('customer/group/edit', ['id' => $id]));
                return;
            }
        } else {
            $this->_forward('new');
        }
    }
}
