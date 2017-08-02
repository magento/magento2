<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Writer\Csv;

use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;
use Magento\Setup\Module\Dependency\Report\WriterInterface;

/**
 * Abstract csv file writer for reports
 * @since 2.0.0
 */
abstract class AbstractWriter implements WriterInterface
{
    /**
     * Csv write object
     *
     * @var \Magento\Framework\File\Csv
     * @since 2.0.0
     */
    protected $writer;

    /**
     * Writer constructor
     *
     * @param \Magento\Framework\File\Csv $writer
     * @since 2.0.0
     */
    public function __construct($writer)
    {
        $this->writer = $writer;
    }

    /**
     * Template method. Main algorithm
     *
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function write(array $options, ConfigInterface $config)
    {
        $this->checkOptions($options);

        $this->writeToFile($options['report_filename'], $this->prepareData($config));
    }

    /**
     * Template method. Check passed options step
     *
     * @param array $options
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function checkOptions($options)
    {
        if (!isset($options['report_filename']) || empty($options['report_filename'])) {
            throw new \InvalidArgumentException('Writing error: Passed option "report_filename" is wrong.');
        }
    }

    /**
     * Template method. Prepare data step
     *
     * @param \Magento\Setup\Module\Dependency\Report\Data\ConfigInterface $config
     * @return array
     * @since 2.0.0
     */
    abstract protected function prepareData($config);

    /**
     * Template method. Write to file step
     *
     * @param string $filename
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    protected function writeToFile($filename, $data)
    {
        $this->writer->saveData($filename, $data);
    }
}
