<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Config\Source;

/**
 * Option provider for the SMTP Transport Type
 */
class SmtpTransportType implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * The possible Transport types
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'sendmail', 'label' => __('Sendmail')],
            ['value' => 'smtp', 'label' => __('SMTP')],
        ];
    }
}
