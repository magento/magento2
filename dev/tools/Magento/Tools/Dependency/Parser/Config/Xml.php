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
namespace Magento\Tools\Dependency\Parser\Config;

use Magento\Tools\Dependency\ParserInterface;

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

        $modules = array();
        foreach ($options['files_for_parse'] as $file) {
            $config = $this->getModuleConfig($file);
            $modules[] = array(
                'name' => $this->extractModuleName($config),
                'dependencies' => $this->extractDependencies($config)
            );
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
     * Template method. Extract dependencies step
     *
     * @param \SimpleXMLElement $config
     * @return array
     */
    protected function extractDependencies($config)
    {
        $dependencies = array();
        /** @var \SimpleXMLElement $dependency */
        if ($config->depends) {
            foreach ($config->depends->module as $dependency) {
                $dependencies[] = array(
                    'module' => $this->prepareModuleName((string)$dependency->attributes()->name),
                    'type' => (string)$dependency->attributes()->type
                );
            }
        }
        return $dependencies;
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
