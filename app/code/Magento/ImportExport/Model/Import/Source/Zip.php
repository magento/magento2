<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Source;

/**
 * Zip import adapter.
 * @since 2.0.0
 */
class Zip extends Csv
{
    /**
     * @param string $file
     * @param \Magento\Framework\Filesystem\Directory\Write $directory
     * @param string $options
     * @since 2.0.0
     */
    public function __construct(
        $file,
        \Magento\Framework\Filesystem\Directory\Write $directory,
        $options
    ) {
        $zip = new \Magento\Framework\Archive\Zip();
        $file = $zip->unpack(
            $directory->getRelativePath($file),
            $directory->getRelativePath(preg_replace('/\.zip$/i', '.csv', $file))
        );
        parent::__construct($file, $directory, $options);
    }
}
