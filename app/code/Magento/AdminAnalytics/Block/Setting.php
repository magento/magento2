<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminAnalytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Config\Model\Config\Factory;

class Setting extends Template
{
    private $configFactory;
    /**
     * @param Context $context
     * @param Factory $configFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $configFactory,
        array $data = []
    ) {
        $this->configFactory = $configFactory;
        parent::__construct($context, $data);
    }

    /**
     * Sets the admin usage's configuration setting to yes
     */
    public function enableAdminUsage()
    {
        $configModel = $this->configFactory->create();
        $configModel->setDataByPath('admin/usage/enabled', 1);
        $configModel->save();
    }

    /**
     * Sets the admin usage's configuration setting to no
     */
    public function disableAdminUsage()
    {
        $configModel = $this->configFactory->create();
        $configModel->setDataByPath('admin/usage/enabled', 0);
        $configModel->save();
    }

    public function showModal() {
        return false;
    }
}
