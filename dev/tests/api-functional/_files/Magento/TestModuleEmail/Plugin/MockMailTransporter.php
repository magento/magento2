<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleEmail\Plugin;

use Magento\Email\Model\Transport;
use Magento\TestModuleEmail\Model\Transport\File;
use Symfony\Component\Mailer\Transport\TransportInterface;

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
