<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Controller\Index;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\PhpEnvironment\Request;

class Post extends \Magento\Contact\Controller\Index
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Post user question
     *
     */
    public function execute()
    {
        if (! $this->isPostRequest()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->inlineTranslation->suspend();
        try {
            $this->sendEmail($this->validatedParams());
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->getDataPersistor()->clear('contact_us');
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
            $this->getDataPersistor()->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
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
     */
    private function sendEmail($post)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(['data' => new \Magento\Framework\DataObject($post)])
            ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
            ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->setReplyTo($post['email'])
            ->getTransport();

        $transport->sendMessage();
    }

    private function isPostRequest()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        return !empty($request->getPostValue());
    }

    private function validatedParams()
    {
        $request = $this->getRequest();
        $error = false;
        if (trim($request->getParam('name')) === '') {
            $error = true;
        }
        if (trim($request->getParam('comment')) === '') {
            $error = true;
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            $error = true;
        }
        if (trim($request->getParam('hideit')) !== '') {
            $error = true;
        }
        if ($error) {
            throw new \Exception();
        }

        return $request->getParams();
    }
}
