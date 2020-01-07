<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Controller\Index;

use Magento\Cms\Helper\Page as CmsHelper;
use Magento\Contact\Controller\Index as AbstractIndex;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Index controller
 */
class Index extends AbstractIndex implements HttpGetActionInterface
{

    /** @var ScopeConfigInterface $scopeConfig */
    private $scopeConfig;

    /** @var CmsHelper $cmsHelper */
    private $cmsHelper;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param CmsHelper $cmsHelper
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        ScopeConfigInterface $scopeConfig,
        CmsHelper $cmsHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cmsHelper = $cmsHelper;
        parent::__construct($context, $contactsConfig);
    }

    /**
     * Show contact us page
     *
     * @return bool|ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $pageIdentifier = $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_CMS_CONTACT_US_PAGE,
            ScopeInterface::SCOPE_STORE
        );

        /** @var Page|bool $resultPage */
        $resultPage = $this->cmsHelper->prepareResultPage($this, $pageIdentifier);

        if ($resultPage === false) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

            return $resultForward->forward('no-route');
        }
        return $resultPage;
    }
}
