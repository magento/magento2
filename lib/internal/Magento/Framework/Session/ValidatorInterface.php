<?php
/**
 * Session validator interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Session validator interface
 */
interface ValidatorInterface
{
    /**
     * Validate session
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return void
     * @throws \Magento\Framework\Session\Exception
     */
    public function validate(\Magento\Framework\Session\SessionManagerInterface $session);
}
