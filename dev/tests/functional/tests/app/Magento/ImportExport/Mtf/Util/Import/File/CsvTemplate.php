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

        $data = include $filename;

        $placeholders = [];
        if (!empty($this->config['placeholders'])) {
            $placeholders = $this->config['placeholders'];
        }

        $fh = fopen('php://temp', 'rw');
        fputcsv($fh, array_keys($data['product_0']['tier_price_0']));

        foreach ($placeholders as $productKey => $tierPrices) {
            foreach ($tierPrices as $tierPriceKey => $tierPriceValue) {
                $row = array_map(
                    function ($value) use ($placeholders, $productKey, $tierPriceKey, $tierPriceValue) {
                        if (is_string($value) && isset($placeholders[$productKey][$tierPriceKey])) {
                            return strtr($value, $tierPriceValue);
                        }
                        return $value;
                    },
                    $data[$productKey][$tierPriceKey]
                );
                fputcsv($fh, $row);

            }
        }

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
     * Return csv data.
     *
     * @return string
     */
    public function getCsv()
    {
        return $this->csv;
    }
}
