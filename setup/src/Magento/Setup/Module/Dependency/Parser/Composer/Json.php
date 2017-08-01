<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Parser\Composer;

use Magento\Framework\Config\Composer\Package;
use Magento\Setup\Module\Dependency\ParserInterface;

/**
 * Composer Json parser
 * @since 2.0.0
 */
class Json implements ParserInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function parse(array $options)
    {
        $this->checkOptions($options);

        $modules = [];
        foreach ($options['files_for_parse'] as $file) {
            $package = $this->getModuleComposerPackage($file);
            $modules[] = [
                'name' => $this->extractModuleName($package),
                'dependencies' => $this->extractDependencies($package),
            ];
        }
        return $modules;
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
     * @param Package $package
     * @return string
     * @since 2.0.0
     */
    protected function extractModuleName($package)
    {
        return $this->prepareModuleName((string)$package->get('name'));
    }

    /**
     * Template method. Extract dependencies step
     *
     * @param Package $package
     * @return array
     * @since 2.0.0
     */
    protected function extractDependencies($package)
    {
        $dependencies = [];
        $requires = $package->get('require', '/.+\/module-/');
        if ($requires) {
            foreach ($requires as $key => $value) {
                $dependencies[] = [
                    'module' => $this->prepareModuleName($key),
                    'type' => 'hard',
                ];
            }
        }

        $suggests = $package->get('suggest', '/.+\/module-/');
        if ($suggests) {
            foreach ($suggests as $key => $value) {
                $dependencies[] = [
                    'module' => $this->prepareModuleName($key),
                    'type' => 'soft',
                ];
            }
        }

        return $dependencies;
    }

    /**
     * Template method. Load module config step
     *
     * @param string $file
     * @return Package
     * @since 2.0.0
     */
    protected function getModuleComposerPackage($file)
    {
        return new Package(json_decode(file_get_contents($file)));
    }

    /**
     * Prepare module name
     *
     * @param string $name
     * @return string
     * @since 2.0.0
     */
    protected function prepareModuleName($name)
    {
        return $name;
    }
}
