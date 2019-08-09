<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Source;

use Magento\Framework\Controller\ResultInterface;

/**
 * MassEnable Controller
 */
class MassEnable extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $massActionFilter;
    /**
     * @var \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory
     */
    protected $sourceCollectionFactory;
    /**
     * @var \Magento\InventoryApi\Api\Data\SourceInterfaceFactory
     */
    private $sourceFactory;
    /**
     * @var \Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    private $sourceRepository;
    /**
     * @var \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;


    /**
     * MassEnable constructor.
     * @param \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface $defaultSourceProvider
     * @param \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
     * @param \Magento\Backend\App\Action\Context                             $context
     * @param \Magento\Ui\Component\MassAction\Filter                         $massActionFilter
     * @param \Magento\InventoryApi\Api\Data\SourceInterfaceFactory           $sourceFactory
     * @param \Magento\InventoryApi\Api\SourceRepositoryInterface             $sourceRepository
     */
    public function __construct(
        \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface $defaultSourceProvider,
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \Magento\InventoryApi\Api\Data\SourceInterfaceFactory $sourceFactory,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository
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

        /** @var \Magento\Inventory\Model\ResourceModel\Source\Collection $sourceCollection */
        $sourceCollection = $this->sourceCollectionFactory->create();
        $this->massActionFilter->getCollection($sourceCollection);

        $enabled = 0;
        $alreadyEnabled = 0;
        /** @var \Magento\Inventory\Model\Source $source */
        foreach ($sourceCollection as $source) {
            $alreadyEnabled++;
            if (!$source->isEnabled() && $source->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                try {
                    $source->setEnabled(true);
                    $this->sourceRepository->save($source);
                    $alreadyEnabled--;
                    $enabled++;
                } catch (\Magento\Framework\Validation\ValidationException $validationException) {
                    $messages = [$validationException->getMessage()];
                    foreach ($validationException->getErrors() as $validationError) {
                        $messages[] = $validationError->getMessage();
                    }
                    $this->messageManager->addErrorMessage(implode(', ', $messages));
                } catch (\Exception $e) {
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

        return $resultRedirect;
    }
}
