<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportExport\Model\Import\Source;

use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import\AbstractSource;

class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $source
     * @param $directory
     * @param $options
     * @return AbstractSource
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
                'source' => $source,
                'directory' => $directory,
                'options' => $options
            ]
        );
    }
}
