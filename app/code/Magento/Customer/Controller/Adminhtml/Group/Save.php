<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Save
 *
 * @package Magento\Customer\Controller\Adminhtml\Group
 */
class Save extends \Magento\Customer\Controller\Adminhtml\Group implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * deprecated 102.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupDataFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param DataPersistorInterface|null $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupDataFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        DataPersistorInterface $dataPersistor = null
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $groupRepository,
            $groupDataFactory,
            $resultForwardFactory,
            $resultPageFactory
        );
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataPersistor = $dataPersistor ?:
            $this->_objectManager->get(DataPersistorInterface::class);
    }

    /**
     * Save customer group.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $groupId = $this->getRequest()->getParam('customer_group_id');

            $customerGroupCode = $data['customer_group_code'];
            $group = $this->groupRepository->getById($groupId);
            if (!$group->getCode() && $groupId) {
                $this->messageManager->addErrorMessage(__('This Customer Group no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $customerGroupCode = $customerGroupCode ?: $group->getCode();

            $group->setCode(!empty($customerGroupCode) ? $customerGroupCode : null);
            $group->setTaxClassId($data['tax_class_id']);

            try {
                $this->groupRepository->save($group);
                $this->messageManager->addSuccessMessage(__('You saved the Customer Group.'));
                $this->dataPersistor->clear('customer_group');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $group->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Customer Group.'));
            }

            $this->dataPersistor->set('customer_group', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Store Customer Group Data to session
     *
     * @deprecated 102.0.0
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
}
