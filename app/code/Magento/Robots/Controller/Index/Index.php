<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Processes request to robots.txt file and returns robots.txt data as result
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->resultRawFactory = $resultRawFactory;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    /**
     * Generates robots.txt data and returns it as result
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $content = $this->scopeConfig->getValue(
            'design/search_engine_robots/custom_instructions',
            ScopeInterface::SCOPE_WEBSITE
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($content);
        return $resultRaw;
    }
}
