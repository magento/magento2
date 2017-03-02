<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Block;

/**
 * Provide scope (website, store or store_group) information on front
 * Can be used, for example, on store front, in order to determine that private cache invalid for current scope, by comparing
 * with appropriate value in store front private cache.
 */
class ScopeProvider extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @inheritdoc
     * @return string - Return scope data in Json format
     */
    public function getScopeConfig()
    {
        $scopeData = [
            'websiteId' => $this->_storeManager->getStore()->getWebsiteId(),
        ];

        return $this->jsonEncoder->encode($scopeData);
    }
}
