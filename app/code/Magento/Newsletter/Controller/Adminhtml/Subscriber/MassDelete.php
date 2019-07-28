<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Newsletter\Controller\Adminhtml\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Subscriber mass delete controller.
 */
class MassDelete extends Subscriber implements HttpPostActionInterface
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param SubscriberFactory|null $subscriberFactory
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
     * Delete one or more subscribers action
     *
     * @return void
     */
    public function execute()
    {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addError(__('Please select one or more subscribers.'));
        } else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->subscriberFactory->create()->load(
                        $subscriberId
                    );
                    $subscriber->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
