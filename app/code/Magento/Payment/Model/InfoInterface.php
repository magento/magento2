<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

interface InfoInterface
{
    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data);

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data);
}
