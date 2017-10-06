<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Order;

class AddressSave extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->regionFactory = $regionFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    /**
     * Save order address
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        /** @var $address \Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Sales\Model\Order\Address */
        $address = $this->_objectManager->create(
            \Magento\Sales\Api\Data\OrderAddressInterface::class
        )->load($addressId);
        $data = $this->getRequest()->getPostValue();
        $this->updateRegionData($data);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->save();
                $this->_eventManager->dispatch(
                    'admin_sales_order_address_update',
                    [
                        'order_id' => $address->getParentId()
                    ]
                );
                $this->messageManager->addSuccess(__('You updated the order address.'));
                return $resultRedirect->setPath('sales/*/view', ['order_id' => $address->getParentId()]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t update the order address right now.'));
            }
            return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
        } else {
            return $resultRedirect->setPath('sales/*/');
        }
    }
    
    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }
    }
}
