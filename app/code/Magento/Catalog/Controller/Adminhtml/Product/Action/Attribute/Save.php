<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;

/**
 * Class Save
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Bulk\BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory
     * @param \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement,
        \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        parent::__construct($context, $attributeHelper);
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
    }

    /**
     * Update product attributes
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }

        /* Collect Data */
        $attributesData = $this->getRequest()->getParam('attributes', []);

        $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', []);
        $websiteAddData = $this->getRequest()->getParam('add_website_ids', []);

        $storeId = $this->attributeHelper->getSelectedStoreId();
        $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);

        $productIds = $this->attributeHelper->getProductIds();

        try {
            $this->publish('product_action_attribute.update', $attributesData, $websiteRemoveData, $websiteAddData, $storeId, $websiteId, $productIds);
            $this->messageManager->addSuccessMessage(__('Message is added to queue'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath(
            'catalog/product/',
            ['store' => $storeId]
        );
    }

    /**
     * Schedule new bulk.
     *
     * @param $queue
     * @param $attributesData
     * @param $websiteRemoveData
     * @param $websiteAddData
     * @param $storeId
     * @param $websiteId
     * @param $productIds
     * @return void
     */
    private function publish($queue, $attributesData, $websiteRemoveData, $websiteAddData, $storeId, $websiteId, $productIds):void
    {
        $operationCount = count($productIds);
        if ($operationCount > 0) {
            $bulkUuid = $this->identityService->generateId();
            $bulkDescription = __('Assign custom prices to selected products');
            $operations = [];
            foreach ($productIds as $productId) {
                $dataToEncode = [
                    'meta_information' => 'ID:' . $productId,
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'website_id' => $websiteId,
                    'website_assign' => $websiteAddData,
                    'website_detach' => $websiteRemoveData,
                    'attributes' => $attributesData
                ];
                $data = [
                    'data' => [
                        'bulk_uuid' => $bulkUuid,
                        'topic_name' => $queue,
                        'serialized_data' => $this->serializer->serialize($dataToEncode),
                        'status' => \Magento\Framework\Bulk\OperationInterface::STATUS_TYPE_OPEN,
                    ]
                ];

                /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
                $operation = $this->operationFactory->create($data);
                $operations[] = $operation;
            }
            $userId = $this->userContext->getUserId();
            $result = $this->bulkManagement->scheduleBulk($bulkUuid, $operations, $bulkDescription, $userId);
            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }
    }

}

