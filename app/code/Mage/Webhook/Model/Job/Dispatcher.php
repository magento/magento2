<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Job_Dispatcher implements Mage_Webhook_Model_Job_Dispatcher_Interface
{
    const SEND_SUCCESS        = 0;
    const SEND_FAILURE        = 1;
    const SEND_NOT_SUBSCRIBED = 2;

    /**
     * XML path to the webhook config
     */
    const XML_PATH_WEBHOOK_SETTINGS = 'global/webhook';

    protected $_config;

    protected $_formatterFactory;

    public function __construct()
    {
        $this->_config         = Mage::getConfig();
        $this->_formatterFactory = Mage::getModel('Mage_Webhook_Model_Formatter_Factory', $this->_config);
    }

    public function dispatch(Mage_Webhook_Model_Job_Interface $job)
    {
        if ($job->getStatus() == Mage_Webhook_Model_Dispatch_Job::FAILED) {
            return false;
        }

        $event      = $job->getEvent();
        $subscriber = $job->getSubscriber();

        // TODO: if event or subscriber do not exist, detect and fail this job

        $transportStatus = self::SEND_FAILURE;
        try {
            if (!$subscriber->isSubscribedToTopic($event->getTopic())) {
                $transportStatus = self::SEND_NOT_SUBSCRIBED;
            } else {
                $response = $this->_sendMessageToSubscriber($event, $subscriber);
                $transportStatus = $this->_getTransportStatus($response);
            }
        } catch (Exception $e) {
            /* important to catch exceptions here so the job status can be updated */
            Mage::logException($e);
        }

        $this->_updateJobStatusBasedOnTransportStatus($transportStatus, $job);

        return true;
    }

    protected function _getTransportStatus($response)
    {
        if ($response->isSuccessful()) {
            return self::SEND_SUCCESS;
        }
        return self::SEND_FAILURE;
    }

    protected function _updateJobStatusBasedOnTransportStatus($status, $job)
    {
        switch ($status) {
            case self::SEND_SUCCESS:
                $job->setStatus(Mage_Webhook_Model_Dispatch_Job::SUCCESS);
                break;
            case self::SEND_NOT_SUBSCRIBED:
                $job->setStatus(Mage_Webhook_Model_Dispatch_Job::FAILED_NOT_SUBSCRIBED);
                break;
            case self::SEND_FAILURE:
                $failureHandler = $this->_getFailureHandler();
                $failureHandler->handleFailure($job);
                break;
            default:
                $job->setStatus(Mage_Webhook_Model_Dispatch_Job::FAILED);
                break;
        }

        $job->save();
    }

    /**
     * @param Mage_Webhook_Model_Event_Interface $event
     * @param Mage_Webhook_Model_Subscriber $subscriber
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function dispatchCallback(Mage_Webhook_Model_Event_Interface $event, Mage_Webhook_Model_Subscriber $subscriber)
    {
        $format = $subscriber->getFormat();
        if (empty($format)) {
            throw Mage::exception('Mage_Webhook',
                Mage::helper('Mage_Webhook_Helper_Data')->__('Format is empty for subscriber with ID: %s',
                    $subscriber->getId()));
        }
        $formatter = $this->_getFormatterFactory()->getFormatterFactory($format)->getFormatter($event->getMapping());

        $message = $formatter->format($event);
        $response = $this->_getTransport()->dispatchMessage($message, $subscriber);

        if ($response->isSuccessful()) {
            $message->setResponseBody($response->getBody());
            $formatter->decode($message);
            return $message;
        }
        throw Mage::exception('Mage_Webhook', 'Callback was not able to retrieve the response');
    }

    protected function _sendMessageToSubscriber(Mage_Webhook_Model_Event_Interface $event,
                                                Mage_Webhook_Model_Subscriber $subscriber)
    {
        $format = $subscriber->getFormat();
        if (empty($format)) {
            Mage::logException(
                Mage::exception('Mage_Webhook',
                    Mage::helper('Mage_Webhook_Helper_Data')
                        ->__('Format is empty for subscriber with ID: %s', $subscriber->getId())));
            return self::SEND_FAILURE;
        }

        /** @var $formatter  */
        $formatter = $this->_getFormatterFactory()->getFormatterFactory($format)->getFormatter($event->getMapping());
        /** @var $message Mage_Webhook_Model_Message_Interface */
        $message = $formatter->format($event);

        $response = $this->_getTransport()->dispatchMessage($message, $subscriber);

        return $response;
    }

    protected function _getFailureHandler()
    {
        return Mage::getModel('Mage_Webhook_Model_Job_Retry_Handler');
    }

    protected function _getFormatterFactory()
    {
        return $this->_formatterFactory;
    }

    protected function _getTransport()
    {
        $type      = Mage::getConfig()->getNode(self::XML_PATH_WEBHOOK_SETTINGS . '/default/transport_type');
        $modelName = Mage::getConfig()->getNode(self::XML_PATH_WEBHOOK_SETTINGS . "/transports/{$type}/class");
        $transport = Mage::getModel((string) $modelName);
        if (!$transport || !$transport instanceof Mage_Webhook_Model_Transport_Interface) {
            throw Mage::exception('Mage_Webhook', Mage::helper('Mage_Webhook_Helper_Data')
                                                        ->__("Cannot find transport model for type %s.", $type)
            );
        }

        return $transport;
    }
}
