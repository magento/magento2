<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleEmail\Plugin;

use Laminas\Mail\Transport\TransportInterface;
use Magento\Email\Model\Transport;
use Magento\TestModuleEmail\Model\Transport\File;

class MockMailTransporter
{
    /**
     * @var File
     */
    private File $file;

    /**
     * @param File $file
     */
    public function __construct(
        File $file
    ) {
        $this->file = $file;
    }

    /**
     * @param Transport $subject
     * @param \Closure $proceed
     * @return TransportInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTransport(
        Transport $subject,
        \Closure $proceed
    ): TransportInterface {
        return $this->file->isEnabled() ? $this->file : $proceed();
    }
}
