<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception;

interface ChainInterface
{
    /**
     * @param string $type
     * @param string $method
     * @param string $subject
     * @param array $arguments
     * @param string $previousPluginCode
     * @return mixed
     */
    public function invokeNext($type, $method, $subject, array $arguments, $previousPluginCode = null);
}
