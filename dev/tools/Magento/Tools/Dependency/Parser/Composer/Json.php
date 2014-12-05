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
namespace Magento\Tools\Dependency\Parser\Composer;

use Magento\Framework\Config\Composer\Package;
use Magento\Tools\Dependency\ParserInterface;

/**
 * Composer Json parser
 */
class Json implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(array $options)
    {
        $this->checkOptions($options);

        $modules = [];
        foreach ($options['files_for_parse'] as $file) {
            $package = $this->getModuleComposerPackage($file);
            $modules[] = [
                'name' => $this->extractModuleName($package),
                'dependencies' => $this->extractDependencies($package)
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
     */
    protected function extractDependencies($package)
    {
        $dependencies = [];
        $requires = $package->get('require', '/.+\/module-/');
        if ($requires) {
            foreach ($requires as $key => $value) {
                $dependencies[] = [
                    'module' => $this->prepareModuleName($key),
                    'type' => 'hard'
                ];
            }
        }

        $suggests = $package->get('suggest', '/.+\/module-/');
        if ($suggests) {
            foreach ($suggests as $key => $value) {
                $dependencies[] = [
                    'module' => $this->prepareModuleName($key),
                    'type' => 'soft'
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
     */
    protected function prepareModuleName($name)
    {
        return $name;
    }
}
