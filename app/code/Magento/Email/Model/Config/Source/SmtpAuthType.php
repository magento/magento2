<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Config\Source;

/**
 * Option provider for the SMTP Auth type
 */
class SmtpAuthType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * The possible Auth types
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'none', 'label' => __('NONE')],
            ['value' => 'plain', 'label' => __('PLAIN')],
            ['value' => 'login', 'label' => __('LOGIN')],
//            ['value' => 'crammd5', 'label' => __('CRAM-MD5) '],  // Requires laminas/laminas-crypt
        ];
    }
}
