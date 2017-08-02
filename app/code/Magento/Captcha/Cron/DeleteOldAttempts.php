<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Cron;

/**
 * Captcha cron actions
 * @since 2.0.0
 */
class DeleteOldAttempts
{
    /**
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     * @since 2.0.0
     */
    protected $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->resLogFactory->create()->deleteOldAttempts();

        return $this;
    }
}
