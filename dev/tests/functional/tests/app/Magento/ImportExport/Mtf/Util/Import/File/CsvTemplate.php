<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Mtf\Util\Import\File;

use Magento\Mtf\Util\Generate\File\TemplateInterface;

/**
 * CSV file template.
 */
class CsvTemplate implements TemplateInterface
{
    /**
     * Configuration.
     *
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        if (!isset($this->config['filename'], $this->config['count'])) {
            throw new \InvalidArgumentException(
                'Configuration for file is bad. You must specify "filename" and "count" in configuration.'
            );
        }

        $filename = MTF_TESTS_PATH . $this->config['filename'] . '.php';

        if (!file_exists($filename)) {
            throw new \Exception('File "' . $filename . '" does not exist.');
        }

        $data = include $filename;
        $count = abs($this->config['count']);

        $placeholders = [];
        if (!empty($this->config['placeholders'])) {
            $placeholders = $this->config['placeholders'];
        }

        $fh = fopen('php://temp', 'rw');
        fputcsv($fh, array_keys($data));

        for ($i = 0; $i < $count; ++$i) {
            $row = array_map(
                function ($value) use ($placeholders, $i) {
                    if (is_string($value) && isset($placeholders[$i])) {
                        return strtr($value, $placeholders[$i]);
                    }

                    return $value;
                },
                $data
            );
            fputcsv($fh, $row);
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return sprintf(
            '%x_%s.csv',
            crc32(time()),
            basename($this->config['filename'])
        );
    }
}
