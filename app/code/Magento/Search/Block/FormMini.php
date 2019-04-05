<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

/**
 * Search form.mini block
 *
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
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Returns serialized config
     *
     * @return mixed
     */
    public function getSerializedConfig()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $searchSuggestionConfig = $this->scopeConfig->getValue(self::XML_PATH_SEARCH_SUGGESTION, $storeScope);

        return $this->serializer->serialize(['searchSuggestionEnabled' => $searchSuggestionConfig]);
    }
}
