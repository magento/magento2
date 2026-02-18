<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
