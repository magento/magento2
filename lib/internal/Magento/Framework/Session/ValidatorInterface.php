<?php
/**
 * Session validator interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
