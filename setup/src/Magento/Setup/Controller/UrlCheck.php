<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class UrlCheck extends AbstractActionController
{
    /**
     * Validate URL
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $result = ['successUrl' => false, 'successSecureUrl' => true];

        // Validating of Base URL
        if (isset($params['address']['actual_base_url'])
            && filter_var($params['address']['actual_base_url'], FILTER_VALIDATE_URL)
        ) {
            $result['successUrl'] = true;
        }

        // Validating of Secure Base URL
        if (!empty($params['https']['admin']) || !empty($params['https']['front'])) {
            if (!(isset($params['https']['text'])
                && filter_var($params['https']['text'], FILTER_VALIDATE_URL))
            ) {
                $result['successSecureUrl'] = false;
            }
        }

        return new JsonModel(array_merge($result));
    }
}
