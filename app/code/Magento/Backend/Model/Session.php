<?php
/**
 * Backend user session
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Skip path validation in backend area
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
