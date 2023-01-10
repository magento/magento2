<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Newsletter\Controller\Adminhtml\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

class MassUnsubscribe extends Subscriber implements HttpPostActionInterface
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        SubscriberFactory $subscriberFactory = null
    ) {
        $this->subscriberFactory = $subscriberFactory ?: ObjectManager::getInstance()->get(SubscriberFactory::class);
        parent::__construct($context, $fileFactory);
    }

    /**
     * Unsubscribe one or more subscribers action
     *
     * @return void
     */
    public function execute(): void
    {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addErrorMessage(__('Please select one or more subscribers.'));
        } else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->subscriberFactory->create()->load(
                        $subscriberId
                    );
                    $subscriber->unsubscribe();
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
