<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class InlineEdit
 */
class InlineEdit extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param Context $context
     * @param HydratorInterface $hydrator
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        Context $context,
        HydratorInterface $hydrator,
        SourceRepositoryInterface $sourceRepository
    ) {
        parent::__construct($context);
        $this->hydrator = $hydrator;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $errorMessages = [];
        $request = $this->getRequest();
        $requestData = $request->getParam('items', []);

        if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
            foreach ($requestData as $itemData) {
                try {
                    $source = $this->sourceRepository->get(
                        $itemData[SourceInterface::SOURCE_ID]
                    );
                    $source = $this->hydrator->hydrate($source, $itemData);
                    $this->sourceRepository->save($source);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __(
                        '[ID: %1] The Source does not exist.',
                        $itemData[SourceInterface::SOURCE_ID]
                    );
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] =
                        __('[ID: %1] ', $itemData[SourceInterface::SOURCE_ID])
                        . $e->getMessage();
                }
            }
        } else {
            $errorMessages[] = __('Please correct the data sent.');
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData([
            'messages' => $errorMessages,
            'error' => count($errorMessages),
        ]);
        return $resultJson;
    }
}
