<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config;

use Magento\Backend\App\Action;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\Controller\Result\JsonFactory;

class TestConnection extends Action
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context    $context
     * @param ClientResolver    $clientResolver
     * @param JsonFactory       $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        ClientResolver $clientResolver,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->clientResolver = $clientResolver;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check for connection to server
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        $options = $this->getRequest()->getParams();

        try {
            if (empty($options['engine'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Missing search engine parameter.')
                );
            }
            $response = $this->clientResolver->create($options['engine'], $options)->testConnection();
            if ($response) {
                $result['success'] = true;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $filter = $this->_objectManager->create('Magento\Framework\Filter\StripTags');
            /* @var $filter \Magento\Framework\Filter\StripTags */
            $message = $filter->filter($e->getMessage());
            $result['errorMessage'] = __($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
