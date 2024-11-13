<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for PayLater banner logo type
 */
class LogoType implements OptionSourceInterface
{
    /**
     * PayLater logo types source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'primary', 'label' => __('Primary')],
            ['value' => 'alternative', 'label' => __('Alternative')],
            ['value' => 'inline', 'label' => __('Inline')],
            ['value' => 'none', 'label' => __('None')]
        ];
    }
}
