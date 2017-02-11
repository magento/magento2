<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Controller\Adminhtml\BasicTier;

use Magento\Backend\App\Action;
use Magento\Config\Model\Config;
use Magento\Backend\App\Action\Context;

/**
 * Class SignUp
 *
 * Provides link to Basic Tier signup
 */
class SignUp extends Action
{
    /**
     * @var string
     */
    private $basicTierUrlPath = 'analytics/url/basic_tier';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Config $config
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
        return $this->_authorization->isAllowed('Magento_Analytics::report_basic_tier');
    }

    /**
     * Provides link to Basic Tier signup
     *
     * @return \Magento\Framework\Controller\AbstractResult
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setUrl(
            $this->config->getConfigDataValue($this->basicTierUrlPath)
        );
    }
}
