<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Config\Source;

/**
 * Option provider for custom media URL type
 */
class SmtpAuthType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * The the possible Auth types
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'none', 'label' => 'NONE'],
            ['value' => 'plain', 'label' => 'PLAIN'],
            ['value' => 'login', 'label' => 'LOGIN'],
//            ['value' => 'crammd5', 'label' => 'CRAM-MD5 '],  // Requires laminas/laminas-crypt
        ];
    }
}
