<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

class StaticValidator
{
    /**
     * @var ValidatorPluginManager
     */
    protected static $plugins;

    /**
     * Set plugin manager to use for locating validators
     *
     * @param  ValidatorPluginManager|null $plugins
     * @return void
     */
    public static function setPluginManager(ValidatorPluginManager $plugins = null)
    {
        // Don't share by default to allow different arguments on subsequent calls
        if ($plugins instanceof ValidatorPluginManager) {
            $plugins->setShareByDefault(false);
        }
        static::$plugins = $plugins;
    }

    /**
     * Get plugin manager for locating validators
     *
     * @return ValidatorPluginManager
     */
    public static function getPluginManager()
    {
        if (null === static::$plugins) {
            static::setPluginManager(new ValidatorPluginManager());
        }
        return static::$plugins;
    }

    /**
     * @param  mixed    $value
     * @param  string   $classBaseName
     * @param  array    $args          OPTIONAL
     * @return bool
     */
    public static function execute($value, $classBaseName, array $args = array())
    {
        $plugins = static::getPluginManager();

        $validator = $plugins->get($classBaseName, $args);
        return $validator->isValid($value);
    }
}
