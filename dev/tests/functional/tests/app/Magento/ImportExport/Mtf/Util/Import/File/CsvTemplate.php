<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Csv data.
     *
     * @var string
     */
    private $csv;

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

        $fh = fopen('php://temp', 'rw');
        $fh = $this->addEntitiesData($fh);
        rewind($fh);
        $this->csv = stream_get_contents($fh);
        fclose($fh);

        return $this->csv;
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

    /**
     * Replace placeholders in csv content.
     *
     * @param resource $stream
     * @return resource
     */
    private function addEntitiesData($stream)
    {
        $filename = MTF_TESTS_PATH . $this->config['filename'] . '.php';
        $entitiesData = include $filename;

        $placeholders = [];
        if (!empty($this->config['placeholders'])) {
            $placeholders = $this->config['placeholders'];
        }

        fputcsv($stream, array_keys($entitiesData['entity_0']['data_0']));
        foreach ($placeholders as $entityKey => $entityData) {
            foreach ($entityData as $dataKey => $dataValue) {
                $row = array_map(
                    function ($value) use ($placeholders, $entityKey, $dataKey, $dataValue) {
                        if (is_string($value) && isset($placeholders[$entityKey][$dataKey])) {
                            return strtr($value, $dataValue);
                        }
                        return $value;
                    },
                    $entitiesData[$entityKey][$dataKey]
                );
                fputcsv($stream, $row);
            }
        }
        return $stream;
    }

    /**
     * Return csv data.
     *
     * @return string
     */
    public function getCsv()
    {
        return $this->csv;
    }
}
