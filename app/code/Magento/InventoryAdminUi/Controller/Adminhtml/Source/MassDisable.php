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
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Disable sources mass action controller.
 */
class MassDisable extends Action implements HttpPostActionInterface
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
        $this->disableSources($sourceCollection);

        return $resultRedirect;
    }

    /**
     * Disable sources if possible and add messages to user interface.
     *
     * @param Collection $sourceCollection
     * @return void
     */
    public function disableSources(Collection $sourceCollection): void
    {
        $disabled = 0;
        $alreadyDisabled = 0;
        foreach ($sourceCollection as $source) {
            if ($source->getSourceCode() === $this->defaultSourceProvider->getCode()) {
                $this->messageManager->addNoticeMessage(__('Default Source can not be disabled.'));
                continue;
            }
            $alreadyDisabled++;
            if ($source->isEnabled()) {
                try {
                    $source->setEnabled(false);
                    $this->sourceRepository->save($source);
                    $alreadyDisabled--;
                    $disabled++;
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
        if ($disabled) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 source(s) have been disabled.', $disabled)
            );
        }
        if ($alreadyDisabled) {
            $this->messageManager->addNoticeMessage(
                __('A total of %1 source(s) already disabled.', $alreadyDisabled)
            );
        }
    }
}
