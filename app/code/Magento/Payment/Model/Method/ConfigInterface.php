<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

/**
 * Interface for payment methods config
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
interface ConfigInterface
{
    /**
     * Config field getter
     * The specified key can be either in camelCase or under_score format
     *
     * @param string $key
     * @return mixed
     */
    public function getConfigValue($key);
}
