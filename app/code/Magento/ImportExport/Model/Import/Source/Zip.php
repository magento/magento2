<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Source;

use Magento\Framework\Exception\ValidatorException;

/**
 * Zip import adapter.
 */
class Zip extends Csv
{
    /**
     * @param string $file
     * @param \Magento\Framework\Filesystem\Directory\Write $directory
     * @param string $options
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
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
        if (!$file) {
            throw new ValidatorException(__('Sorry, but the data is invalid or the file is not uploaded.'));
        }
        parent::__construct($file, $directory, $options);
    }
}
