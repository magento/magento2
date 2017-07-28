<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

/**
 * Backend user session
 *
 * @api
 * @since 2.0.0
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Skip path validation in backend area
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
