<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\DeleteWidgetById;

/**
 * Class MassDelete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var DeleteWidgetById
     */
    private $deleteWidgetById;

    /**
     * @param Context $context
     * @param DeleteWidgetById $deleteWidgetById
     */
    public function __construct(
        Context $context,
        DeleteWidgetById $deleteWidgetById
    ) {
        parent::__construct($context);
        $this->deleteWidgetById = $deleteWidgetById;
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws \Exception
     */
    public function execute(): Redirect
    {
        $deletedInstances = 0;
        $notDeletedInstances = [];
        /** @var array $instanceIds */
        $instanceIds = $this->getInstanceIds();

        if (!count($instanceIds)) {
            $this->messageManager->addErrorMessage(__('No widget instance IDs were provided to be deleted.'));

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->getResultPage();

            return $resultRedirect->setPath('*/*/');
        }

        foreach ($instanceIds as $instanceId) {
            try {
                $this->deleteWidgetById->execute((int)$instanceId);
                $deletedInstances++;
            } catch (NoSuchEntityException $e) {
                $notDeletedInstances[] = $instanceId;
            }
        }

        if ($deletedInstances) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $deletedInstances));
        }

        if (count($notDeletedInstances)) {
            $this->messageManager->addErrorMessage(
                __(
                    'Widget(s) with ID(s) %1 were not found',
                    trim(implode(', ', $notDeletedInstances))
                )
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->getResultPage();

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get instance IDs.
     *
     * @return array
     */
    private function getInstanceIds(): array
    {
        $instanceIds = $this->getRequest()->getParam('delete');

        if (!is_array($instanceIds)) {
            return [];
        }

        return $instanceIds;
    }

    /**
     * Get result page.
     *
     * @return ResultInterface|null
     */
    private function getResultPage(): ?ResultInterface
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }
}
