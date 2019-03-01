<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Search form.mini block
 */
namespace Demac\CoreRewrite\Search\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

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
     * @param Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $data = []
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
