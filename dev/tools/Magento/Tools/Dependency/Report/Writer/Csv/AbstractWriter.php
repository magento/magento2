<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Dependency\Report\Writer\Csv;

use Magento\Tools\Dependency\Report\Data\ConfigInterface;
use Magento\Tools\Dependency\Report\WriterInterface;

/**
 * Abstract csv file writer for reports
 */
abstract class AbstractWriter implements WriterInterface
{
    /**
     * Csv write object
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $writer;

    /**
     * Writer constructor
     *
     * @param \Magento\Framework\File\Csv $writer
     */
    public function __construct($writer)
    {
        $this->writer = $writer;
    }

    /**
     * Template method. Main algorithm
     *
     * {@inheritdoc}
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
     * @param \Magento\Tools\Dependency\Report\Data\ConfigInterface $config
     * @return array
     */
    abstract protected function prepareData($config);

    /**
     * Template method. Write to file step
     *
     * @param string $filename
     * @param array $data
     * @return void
     */
    protected function writeToFile($filename, $data)
    {
        $this->writer->saveData($filename, $data);
    }
}
