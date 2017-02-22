<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Cron;

/**
 * Captcha cron actions
 */
class DeleteOldAttempts
{
    /**
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     */
    protected $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     */
    public function __construct(
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
    ) {
        $this->resLogFactory = $resLogFactory;
    }

    /**
     * Delete Unnecessary logged attempts
     *
     * @return \Magento\Captcha\Cron\DeleteOldAttempts
     */
    public function execute()
    {
        $this->resLogFactory->create()->deleteOldAttempts();

        return $this;
    }
}
