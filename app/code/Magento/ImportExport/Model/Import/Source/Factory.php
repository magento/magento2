<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportExport\Model\Import\Source;

use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import\AbstractSource;

class Factory
{
    /**
     * Object Manager Instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $source
     * @param Write $directory
     * @param mixed $options
     * @return AbstractSource
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($source, $directory = null, $options = null): AbstractSource
    {
        $adapterClass = 'Magento\ImportExport\Model\Import\Source\\';
        if (file_exists($source)) {
            $type = ucfirst(strtolower(pathinfo($source, PATHINFO_EXTENSION)));
        } else {
            $type = 'Base64EncodedCsvData';
        }
        if (!is_string($source) || !$source) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The source type must be a non-empty string.')
            );
        }
        $adapterClass.= $type;
        if (!class_exists($adapterClass)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('\'%1\' file extension is not supported', $type)
            );
        }
        return $this->objectManager->create(
            $adapterClass,
            [
                'file' => $source,
                'directory' => $directory,
                'options' => $options
            ]
        );
    }
}
