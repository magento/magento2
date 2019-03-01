<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Search form.mini block
 */
namespace Magento\Search\Block;

use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;

/**
 * @api
 * @since 100.0.2
 */
class FormMini extends Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Search Suggestions Enabled
     */
    const XML_PATH_SEARCH_SUGGESTION = 'catalog/search/search_suggestion_enabled';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);

        parent::__construct($context, $data);
    }


    /**
     * getSerializedConfig
     *
     * @return mixed
     */
    public function getSerializedConfig() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->serializer->serialize([
            'searchSuggestionEnabled' => $this->scopeConfig->getValue(self::XML_PATH_SEARCH_SUGGESTION, $storeScope),
        ]);
    }
    
    
}
