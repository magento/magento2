<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\FileSystem;

/**
 * CSV file operations wrapper.
 */
class Csv
{
    /**
     * Default batch size.
     *
     * @var int
     */
    private $batchSize = 15000;

    /**
     * Save to csv data with batches.
     *
     * @param string $file
     * @param array $data
     * @return $this
     */
    public function save($file, array $data)
    {
        if (!count($data)) {
            return $this;
        }

        if (!file_exists($file)) {
            array_unshift($data, array_keys($data[0]));
        }

        $fh = fopen($file, 'a');

        foreach ($data as $dataRow) {
            fputcsv($fh, $dataRow);
        }

        fclose($fh);
        return $this;
    }

    /**
     * File read generator.
     *
     * @param string $file
     * @return \Generator
     */
    public function readGenerator($file)
    {
        $data = [];
        if (!file_exists($file)) {
            return;
        }

        $iterator = 0;
        $fh = fopen($file, 'r');
        yield fgetcsv($fh);

        while ($rowData = fgetcsv($fh)) {
            if ($iterator++ > $this->batchSize) {
                $iterator = 0;
                $finalData = $data;
                $data = [];
                yield $finalData;
            }

            $data[] = $rowData;
        }

        fclose($fh);
        yield $data;
    }
}
