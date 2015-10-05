<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Cron;

/**
 * Captcha cron actions
 */
class DeleteOldAttempts
{
    /**
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    protected $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\Resource\LogFactory $resLogFactory
     */
    public function __construct(
        \Magento\Captcha\Model\Resource\LogFactory $resLogFactory
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
