<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Provides a list of supported file storages.
 */
class FileStorage implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return $this->options;
    }
}
