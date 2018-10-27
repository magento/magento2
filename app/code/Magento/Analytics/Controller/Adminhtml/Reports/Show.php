<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Controller\Adminhtml\Reports;

<<<<<<< HEAD
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
=======
>>>>>>> upstream/2.2-develop
use Magento\Analytics\Model\Exception\State\SubscriptionUpdateException;
use Magento\Analytics\Model\ReportUrlProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Provide redirect to resource with reports.
 */
<<<<<<< HEAD
class Show extends Action implements HttpGetActionInterface
=======
class Show extends Action
>>>>>>> upstream/2.2-develop
{
    /**
     * @var ReportUrlProvider
     */
    private $reportUrlProvider;

    /**
<<<<<<< HEAD
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Magento_Analytics::analytics_settings';

    /**
=======
>>>>>>> upstream/2.2-develop
     * @param Context $context
     * @param ReportUrlProvider $reportUrlProvider
     */
    public function __construct(
        Context $context,
        ReportUrlProvider $reportUrlProvider
    ) {
        $this->reportUrlProvider = $reportUrlProvider;
        parent::__construct($context);
    }

    /**
<<<<<<< HEAD
=======
     * Check admin permissions for this controller.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Analytics::analytics_settings');
    }

    /**
>>>>>>> upstream/2.2-develop
     * Redirect to resource with reports.
     *
     * @return Redirect $resultRedirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $resultRedirect->setUrl($this->reportUrlProvider->getUrl());
        } catch (SubscriptionUpdateException $e) {
            $this->getMessageManager()->addNoticeMessage($e->getMessage());
            $resultRedirect->setPath('adminhtml');
        } catch (LocalizedException $e) {
            $this->getMessageManager()->addExceptionMessage($e, $e->getMessage());
            $resultRedirect->setPath('adminhtml');
        } catch (\Exception $e) {
            $this->getMessageManager()->addExceptionMessage(
                $e,
                __('Sorry, there has been an error processing your request. Please try again later.')
            );
            $resultRedirect->setPath('adminhtml');
        }

        return $resultRedirect;
    }
}
