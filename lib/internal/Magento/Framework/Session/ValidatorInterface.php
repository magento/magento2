<?php
/**
 * Session validator interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Session validator interface
 * @since 2.0.0
 */
interface ValidatorInterface
{
    /**
     * Validate session
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return void
     * @throws \Magento\Framework\Exception\SessionException
     * @since 2.0.0
     */
    public function validate(\Magento\Framework\Session\SessionManagerInterface $session);
}
