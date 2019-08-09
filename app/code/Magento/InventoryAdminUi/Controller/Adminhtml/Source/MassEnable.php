<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Source;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\ResourceModel\Source\Collection;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\Inventory\Model\Source;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Enable source mass action controller.
 */
class MassEnable extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

    /**
     * @var Filter
     */
    private $massActionFilter;

    /**
     * @var CollectionFactory
     */
    private $sourceCollectionFactory;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param CollectionFactory $sourceCollectionFactory
     * @param Context $context
     * @param Filter $massActionFilter
     * @param SourceInterfaceFactory $sourceFactory
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        CollectionFactory $sourceCollectionFactory,
        Context $context,
        Filter $massActionFilter,
        SourceInterfaceFactory $sourceFactory,
        SourceRepositoryInterface $sourceRepository
    ) {
        parent::__construct($context);
        $this->massActionFilter = $massActionFilter;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('inventory/source/index');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            return $resultRedirect;
        }
        $sourceCollection = $this->sourceCollectionFactory->create();
        $this->massActionFilter->getCollection($sourceCollection);
        $this->enableSources($sourceCollection);

        return $resultRedirect;
    }

    /**
     * Enable sources and add messages to user interface.
     *
     * @param Collection $sourceCollection
     * @return void
     */
    public function enableSources(Collection $sourceCollection): void
    {
        $enabled = 0;
        $alreadyEnabled = 0;
        foreach ($sourceCollection as $source) {
            $alreadyEnabled++;
            if (!$source->isEnabled() && $source->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                try {
                    $source->setEnabled(true);
                    $this->sourceRepository->save($source);
                    $alreadyEnabled--;
                    $enabled++;
                } catch (ValidationException $validationException) {
                    $messages = [$validationException->getMessage()];
                    foreach ($validationException->getErrors() as $validationError) {
                        $messages[] = $validationError->getMessage();
                    }
                    $this->messageManager->addErrorMessage(implode(', ', $messages));
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
        if ($enabled) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 source(s) have been enabled.', $enabled)
            );
        }
        if ($alreadyEnabled) {
            $this->messageManager->addNoticeMessage(
                __('A total of %1 source(s) already enabled.', $alreadyEnabled)
            );
        }
    }
}
