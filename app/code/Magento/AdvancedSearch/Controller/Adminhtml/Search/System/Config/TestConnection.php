<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;

class TestConnection extends Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_CatalogSearch::config_catalog_search';

    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @param Context           $context
     * @param ClientResolver    $clientResolver
     * @param JsonFactory       $resultJsonFactory
     * @param StripTags         $tagFilter
     */
    public function __construct(
        Context $context,
        ClientResolver $clientResolver,
        JsonFactory $resultJsonFactory,
        StripTags $tagFilter
    ) {
        parent::__construct($context);
        $this->clientResolver = $clientResolver;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
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
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
