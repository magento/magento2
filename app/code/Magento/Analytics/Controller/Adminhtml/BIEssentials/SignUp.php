<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Controller\Adminhtml\BIEssentials;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class SignUp
 *
 * Provides link to BI Essentials signup
 */
class SignUp extends Action
{
    /**
     * Path to config value with URL to BI Essentials sign-up page.
     *
     * @var string
     */
    private $urlBIEssentialsConfigPath = 'analytics/url/bi_essentials';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Analytics::bi_essentials');
    }

    /**
     * Provides link to BI Essentials signup
     *
     * @return \Magento\Framework\Controller\AbstractResult
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setUrl(
            $this->config->getValue($this->urlBIEssentialsConfigPath)
        );
    }
}
