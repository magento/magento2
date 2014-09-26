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

namespace Magento\Config;

use Zend\Config\Config as ZendConfig;
use Zend\Filter\Inflector;

class Config extends ZendConfig
{
    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @param Inflector $inflector
     * @param array $array
     */
    public function __construct(Inflector $inflector, array $array)
    {
        $this->inflector = $inflector;
        $this->inflector->setTarget(':name')
            ->setRules([':name' => ['Word\CamelCaseToUnderscore', 'StringToLower']]);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new static($this->inflector, $value);
            } else {
                $this->data[$key] = $value;
            }
            $this->count++;
        }
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $name = $this->inflector->filter(['name' => $name]);

        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * Retrieve Magento base path
     *
     * @return string
     */
    public function getMagentoBasePath()
    {
        return $this->magento->basePath;
    }

    /**
     * Retrieve path to Magento modules
     *
     * @return string
     */
    public function getMagentoModulePath()
    {
        return $this->magento->filesystem->module;
    }

    /**
     * Retrieve the list of Magento file permissions
     *
     * @return mixed
     */
    public function getMagentoFilePermissions()
    {
        return $this->magento->filesystem->permissions;
    }

    /**
     * Retrieve path to Magento config directory
     *
     * @return mixed
     */
    public function getMagentoConfigPath()
    {
        return $this->magento->filesystem->config;
    }
}
