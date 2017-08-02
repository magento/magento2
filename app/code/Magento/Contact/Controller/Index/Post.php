<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Controller\Index;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Psr\Log\LoggerInterface;

/**
 * Class \Magento\Contact\Controller\Index\Post
 *
 * @since 2.0.0
 */
class Post extends \Magento\Contact\Controller\Index
{
    /**
     * @var DataPersistorInterface
     * @since 2.1.0
     */
    private $dataPersistor;

    /**
     * @var Context
     * @since 2.2.0
     */
    private $context;

    /**
     * @var MailInterface
     * @since 2.2.0
     */
    private $mail;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $contactsConfig);
        $this->context = $context;
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Post user question
     *
     * @return Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        if (!$this->isPostRequest()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $this->sendEmail($this->validatedParams());
            $this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->getDataPersistor()->clear('contact_us');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->getDataPersistor()->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->getDataPersistor()->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     * @since 2.1.0
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     * @since 2.2.0
     */
    private function sendEmail($post)
    {
        $this->mail->send($post['email'], ['data' => new \Magento\Framework\DataObject($post)]);
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    private function isPostRequest()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        return !empty($request->getPostValue());
    }

    /**
     * @return array
     * @throws \Exception
     * @since 2.2.0
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Name is missing'));
        }
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Comment is missing'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('Invalid email address'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }

        return $request->getParams();
    }
}
