<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Controller\Adminhtml\Search\System\Config\TestConnection;

use Magento\Backend\App\Action;
use Magento\AdvancedSearch\Model\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\FactoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Ping extends \Magento\Backend\App\Action
{
    /**
     * @var FactoryInterface
     */
    private $clientFactory;

    /**
     * @var ClientInterface
     */
    private $clientHelper;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param FactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        FactoryInterface $clientFactory,
        ClientOptionsInterface $clientHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->clientFactory = $clientFactory;
        $this->clientHelper = $clientHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check for connection to server
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'error_message' => __('Please check your credentials.')
        ];
        $options = [
            'hostname' => $this->getRequest()->getParam('host'),
            'port' => $this->getRequest()->getParam('port'),
            'auth' => $this->getRequest()->getParam('auth'),
            'username' => $this->getRequest()->getParam('username'),
            'pass' => $this->getRequest()->getParam('pass'),
        ];
        if ($this->validateParams($options)) {
            $options['timeout'] = (int)$this->getRequest()->getParam('timeout');

            $result['error_message'] = '';
            try {
                $response = $this->clientFactory->create($this->clientHelper->prepareClientOptions($options))->ping();
                if (isset($response['status']) && strcasecmp($response['status'], 'ok') == 0) {
                    $result['success'] = true;
                }
            } catch (\Exception $e) {
                $result['error_message'] = strip_tags($e->getMessage());
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }

    /**
     * Validate options
     *
     * @param array $params
     * @return bool
     */
    private function validateParams(array $params)
    {
        if ($params['auth'] == '0') {
            unset($params['username']);
            unset($params['pass']);
            unset($params['auth']);
        }

        return array_reduce($params, function ($valid, $item) {
            return $valid && !empty($item);
        }, true);
    }
}
