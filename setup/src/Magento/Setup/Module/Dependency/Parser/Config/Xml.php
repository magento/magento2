<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Parser\Config;

use Magento\Setup\Module\Dependency\ParserInterface;

/**
 * Config xml parser
 */
class Xml implements ParserInterface
{
    /**
     * Template method. Main algorithm
     *
     * {@inheritdoc}
     */
    public function parse(array $options)
    {
        $this->checkOptions($options);

        $modules = [];
        foreach ($options['files_for_parse'] as $file) {
            $config = $this->getModuleConfig($file);
            $modules[] = $this->extractModuleName($config);
        }
        return $modules;
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
        if (!isset(
            $options['files_for_parse']
        ) || !is_array(
            $options['files_for_parse']
        ) || !$options['files_for_parse']
        ) {
            throw new \InvalidArgumentException('Parse error: Option "files_for_parse" is wrong.');
        }
    }

    /**
     * Template method. Extract module step
     *
     * @param \SimpleXMLElement $config
     * @return string
     */
    protected function extractModuleName($config)
    {
        return $this->prepareModuleName((string)$config->attributes()->name);
    }

    /**
     * Template method. Load module config step
     *
     * @param string $file
     * @return \SimpleXMLElement
     */
    protected function getModuleConfig($file)
    {
        return \simplexml_load_file($file)->xpath('/config/module')[0];
    }

    /**
     * Prepare module name
     *
     * @param string $name
     * @return string
     */
    protected function prepareModuleName($name)
    {
        return str_replace('_', '\\', $name);
    }
}
