<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Contact\Model\MailParamsValidator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

class Post extends \Magento\Contact\Controller\Index implements HttpPostActionInterface
{
    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var MailParamsValidator
     */
    private $paramsValidator;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param MailParamsValidator $paramsValidator
     * @param DataObjectFactory $dataObjectFactory
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        MailParamsValidator $paramsValidator,
        DataObjectFactory $dataObjectFactory,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $contactsConfig);
        $this->mail = $mail;
        $this->paramsValidator = $paramsValidator;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $params = $this->dataObjectFactory->create(['data' => $this->getRequest()->getParams()]);
            $this->validatedParams($params);
            $this->sendEmail($params);
            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('contact_us');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    /**
     * @param DataObject $params Post data from contact form transformed into a Data Object
     * @return void
     */
    private function sendEmail(DataObject $params): void
    {
        $this->mail->send(
            $params->getData('email'),
            ['data' => $params]
        );
    }

    /**
     * @param DataObject $params
     * @throws \Exception
     */
    private function validatedParams(DataObject $params): void
    {
        $this->paramsValidator->validate($params);

        // Validation relevant only for non-headless storefronts
        if (trim($params->getData('hideit')) !== '') {
            throw new \Exception();
        }
    }
}
