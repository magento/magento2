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
 * @since 2.2.0
 */
class SignUp extends Action
{
    /**
     * Path to config value with URL to BI Essentials sign-up page.
     *
     * @var string
     * @since 2.2.0
     */
    private $urlBIEssentialsConfigPath = 'analytics/url/bi_essentials';

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $config;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $config
     * @since 2.2.0
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
     * @since 2.2.0
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Analytics::bi_essentials');
    }

    /**
     * Provides link to BI Essentials signup
     *
     * @return \Magento\Framework\Controller\AbstractResult
     * @since 2.2.0
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setUrl(
            $this->config->getValue($this->urlBIEssentialsConfigPath)
        );
    }
}
