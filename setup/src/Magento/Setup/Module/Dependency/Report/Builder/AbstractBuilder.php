<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Builder;

use Magento\Setup\Module\Dependency\ParserInterface;
use Magento\Setup\Module\Dependency\Report\BuilderInterface;
use Magento\Setup\Module\Dependency\Report\WriterInterface;

/**
 *  Abstract report builder by config files
 */
abstract class AbstractBuilder implements BuilderInterface
{
    /**
     * Dependencies parser
     *
     * @var \Magento\Setup\Module\Dependency\ParserInterface
     */
    protected $dependenciesParser;

    /**
     * Report writer
     *
     * @var \Magento\Setup\Module\Dependency\Report\WriterInterface
     */
    protected $reportWriter;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Builder constructor
     *
     * @param ParserInterface $dependenciesParser
     * @param WriterInterface $reportWriter
     */
    public function __construct(ParserInterface $dependenciesParser, WriterInterface $reportWriter)
    {
        $this->dependenciesParser = $dependenciesParser;
        $this->reportWriter = $reportWriter;
    }

    /**
     * Template method. Main algorithm
     *
     * {@inheritdoc}
     */
    public function build(array $options)
    {
        $this->checkOptions($options);
        $this->options = $options;

        $config = $this->buildData($this->dependenciesParser->parse($options['parse']));
        $this->reportWriter->write($options['write'], $config);
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
        if (!isset($options['parse']) || empty($options['parse'])) {
            throw new \InvalidArgumentException('Passed option section "parse" is wrong.');
        }

        if (!isset($options['write']) || empty($options['write'])) {
            throw new \InvalidArgumentException('Passed option section "write" is wrong.');
        }
    }

    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Setup\Module\Dependency\Report\Data\ConfigInterface
     */
    abstract protected function buildData($modulesData);
}
