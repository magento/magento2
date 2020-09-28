<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Controller\Term;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\ForwardFactory as ResultForwardFactory;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Popular search terms page
 */
class Popular extends Action implements HttpGetActionInterface
{
    private const XML_PATH_SEO_SEARCH_TERMS = 'catalog/seo/search_terms';

    /**
     * @var ResultForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var ResultPageFactory
     */
    private $resultPageFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param ResultForwardFactory $resultForwardFactory
     * @param ResultPageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ResultForwardFactory $resultForwardFactory,
        ResultPageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->checkEnabledSearchTerms()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');

            return $resultForward;
        }

        return $this->resultPageFactory->create();
    }

    /**
     * Check if search terms are enabled
     *
     * @return bool
     */
    private function checkEnabledSearchTerms(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEO_SEARCH_TERMS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
