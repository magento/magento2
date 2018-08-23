<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Block;

use Magento\Checkout\Block\Onepage;

/**
 * Class ResetCheckoutConfigOnOnePage
 * Needed for reformat Customer Data address with custom attributes as options add labels for correct view on UI OnePage
 */
class ResetCheckoutConfigOnOnePage extends AbstractResetCheckoutConfig
{
    /**
     * After Get Checkout Config
     *
     * @param Onepage $subject
     * @param mixed $result
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function afterGetSerializedCheckoutConfig(Onepage $subject, $result)
    {
        return $this->getSerializedCheckoutConfig($subject, $result);
    }
}
