<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

interface ChainInterface
{
    /**
     * @param string $type
     * @param string $method
     * @param InterceptorInterface $subject
     * @param array $arguments
     * @param string $previousPluginCode
     * @return mixed
     */
    public function invokeNext(
        $type,
        $method,
        InterceptorInterface $subject,
        array $arguments,
        $previousPluginCode = null
    );
}
