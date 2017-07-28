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
 * @since 2.0.0
 */
abstract class AbstractBuilder implements BuilderInterface
{
    /**
     * Dependencies parser
     *
     * @var \Magento\Setup\Module\Dependency\ParserInterface
     * @since 2.0.0
     */
    protected $dependenciesParser;

    /**
     * Report writer
     *
     * @var \Magento\Setup\Module\Dependency\Report\WriterInterface
     * @since 2.0.0
     */
    protected $reportWriter;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $options = [];

    /**
     * Builder constructor
     *
     * @param ParserInterface $dependenciesParser
     * @param WriterInterface $reportWriter
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function buildData($modulesData);
}
