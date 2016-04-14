<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Framework;

use Magento\Setup\Module\Dependency\ParserInterface;
use Magento\Setup\Module\Dependency\Report\Builder\AbstractBuilder;
use Magento\Setup\Module\Dependency\Report\WriterInterface;

/**
 *  Framework dependencies report builder
 */
class Builder extends AbstractBuilder
{
    /**
     * Config parser
     *
     * @var \Magento\Setup\Module\Dependency\ParserInterface
     */
    protected $configParser;

    /**
     * Builder constructor
     *
     * @param ParserInterface $dependenciesParser
     * @param WriterInterface $reportWriter
     * @param ParserInterface $configParser
     */
    public function __construct(
        ParserInterface $dependenciesParser,
        WriterInterface $reportWriter,
        ParserInterface $configParser
    ) {
        parent::__construct($dependenciesParser, $reportWriter);

        $this->configParser = $configParser;
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
        parent::checkOptions($options);

        if (!isset($options['parse']['config_files']) || empty($options['parse']['config_files'])) {
            throw new \InvalidArgumentException('Parse error. Passed option "config_files" is wrong.');
        }
    }

    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Setup\Module\Dependency\Report\Framework\Data\Config
     */
    protected function buildData($modulesData)
    {
        $allowedModules = $this->getAllowedModules();

        $modules = [];
        foreach ($modulesData as $moduleData) {
            $dependencies = [];
            foreach ($moduleData['dependencies'] as $dependencyData) {
                if (!in_array($dependencyData['lib'], $allowedModules)) {
                    $dependencies[] = new Data\Dependency($dependencyData['lib'], $dependencyData['count']);
                }
            }
            $modules[] = new Data\Module($moduleData['name'], $dependencies);
        }
        return new Data\Config($modules);
    }

    /**
     * Get allowed modules
     *
     * @return array
     */
    protected function getAllowedModules()
    {
        return $this->configParser->parse(['files_for_parse' => $this->options['parse']['config_files']]);
    }
}
