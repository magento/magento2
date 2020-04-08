<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Magento\Setup\Validator\ElasticsearchConnectionValidator;

/**
 * SearchEngineCheck controller
 */
class SearchEngineCheck extends AbstractActionController
{
    /**
     * @var ElasticsearchConnectionValidator
     */
    private $connectionValidator;

    /**
     * @param ElasticsearchConnectionValidator $connectionValidator
     */
    public function __construct(ElasticsearchConnectionValidator $connectionValidator)
    {
        $this->connectionValidator = $connectionValidator;
    }

    /**
     * Result of checking Elasticsearch connection
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        try {
            $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
            $this->connectionValidator->isValidConnection(
                [
                    'hostname' => $params['elasticsearch']['hostname'] ?? null,
                    'port' => $params['elasticsearch']['port'] ?? null,
                    'enableAuth' => $params['elasticsearch']['enableAuth'] ?? false,
                    'username' => $params['elasticsearch']['username'] ?? null,
                    'password' => $params['elasticsearch']['password'] ?? null,
                    'indexPrefix' => $params['elasticsearch']['indexPrefix'] ?? ''
                ]
            );
            return new JsonModel(['success' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
