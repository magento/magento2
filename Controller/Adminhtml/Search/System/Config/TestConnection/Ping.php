<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection;

use Magento\Backend\App\Action;
use Magento\AdvancedSearch\Model\Client\ClientPool;
use Magento\Framework\Controller\Result\JsonFactory;

class Ping extends \Magento\Backend\App\Action
{
    /**
     * @var ClientPool
     */
    private $clientPool;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context    $context
     * @param ClientPool        $clientPool
     * @param JsonFactory       $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        ClientPool $clientPool,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->clientPool = $clientPool;
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
            $response = $this->clientPool->create($options['engine'], $options)->validateConnectionParameters();
            if ($response) {
                $result['success'] = true;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = strip_tags($e->getMessage());
            $result['errorMessage'] = __($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
