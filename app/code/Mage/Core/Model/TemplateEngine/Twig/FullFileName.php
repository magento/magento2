<?php
/**
 * Custom Loader that gets the contents of the file name from provided absolute path
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_TemplateEngine_Twig_FullFileName implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    /**
     * Caches the exists of a template so that we don't have to check the disk every time.
     *
     * @var array
     */
    private $_existsCache = array();
    
    /**
     * @var Mage_Core_Model_App_State
     */
    private $_appState;
    
    /**
     * Create new instance of FullFileName loader
     *
     * @param Mage_Core_Model_App_State
     */
    public function __construct(Mage_Core_Model_App_State $appState)
    {
        $this->_appState = $appState;
    }
    
    
    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     * @return string The template source code
     * @throws Twig_Error_Loader When $name is not found
     */
    public function getSource($name) 
    {
        $return = file_get_contents($name);
        if ($return === false) {
            throw new Twig_Error_Loader(sprintf('Unable to find "%s".', $name));
        }
        // add to cache
        $this->exists($name);
        return $return;
    }
    
    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        return $name;
    }
    
    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int $time The last modification time of the cached template
     * @return Boolean true if the template is fresh, false otherwise
     * @throws Twig_Error_Loader When last-modified time of $name cannot be found
     */
    public function isFresh($name, $time) 
    {
        if ($this->_appState->getMode() === Mage_Core_Model_App_State::MODE_DEVELOPER) {
            $lastModifiedTime = filemtime($name);
            if ($lastModifiedTime === false) {
                throw new Twig_Error_Loader(sprintf('Could not get last-modified time for "%s".', $name));
            }
            return $lastModifiedTime < $time;
        }

        return true;
    }

    /**
     * Determines whether the template exists or not.
     *
     * Since the template name is interpreted as a fully-qualified path,
     * this is equivalent to checking whether
     * the file at the given location exists.
     *
     * @param string $name
     * @return bool
     * @throws Twig_Error_Loader if $name is not a file
     */
    public function exists($name)
    {
        if (!isset($this->_existsCache[$name])) {
            $this->_existsCache[$name] = file_exists($name);
        }
        return $this->_existsCache[$name];
    }
}
