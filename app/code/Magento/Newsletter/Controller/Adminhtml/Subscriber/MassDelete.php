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

class MassDelete extends Subscriber
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    
    /**
     * @param Context $context
     * @param FileFactory $fileFactory
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
            $this->messageManager->addErrorMessage(__('Please select one or more subscribers.'));
        } else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->subscriberFactory->create()->load(
                        $subscriberId
                    );
                    $subscriber->delete();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) were deleted.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
