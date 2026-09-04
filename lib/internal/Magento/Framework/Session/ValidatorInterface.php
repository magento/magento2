<?php
/**
 * Session validator interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

/**
 * Session validator interface
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validate session
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return void
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function validate(\Magento\Framework\Session\SessionManagerInterface $session);
}
