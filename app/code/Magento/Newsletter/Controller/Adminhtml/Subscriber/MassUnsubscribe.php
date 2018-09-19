<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

use Magento\Newsletter\Controller\Adminhtml\Subscriber;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\App\ObjectManager;

class MassUnsubscribe extends Subscriber
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
                    $subscriber->unsubscribe();
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
