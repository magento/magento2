<?php
/**
 * Primary application config (app/etc/*.xml)
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
namespace Magento\Core\Model\Config;

class Primary extends \Magento\Core\Model\Config\Base
{
    /**
     * @var \Magento\Core\Model\Config\Loader\Primary
     */
    protected $_loader;

    /**
     * Application parameter list
     *
     * @var array
     */
    protected $_params;

    /**
     * Directory list
     *
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @param string $baseDir
     * @param array $params
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\Config\LoaderInterface $loader
     */
    public function __construct(
        $baseDir,
        array $params,
        \Magento\App\Dir $dir = null,
        \Magento\Core\Model\Config\LoaderInterface $loader = null
    ) {
        parent::__construct('<config/>');
        $this->_params = $params;
        $this->_dir = $dir ?: new \Magento\App\Dir(
            $baseDir,
            $this->getParam(\Magento\Core\Model\App::PARAM_APP_URIS, array()),
            $this->getParam(\Magento\Core\Model\App::PARAM_APP_DIRS, array())
        );
        \Magento\Autoload\IncludePath::addIncludePath(array(
            $this->_dir->getDir(\Magento\App\Dir::GENERATION)
        ));

        $this->_loader = $loader ?: new \Magento\Core\Model\Config\Loader\Primary(
            $this->_dir->getDir(\Magento\App\Dir::CONFIG)
        );
        $this->_loader->load($this);
    }

    /**
     * Get init param
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getParam($name, $defaultValue = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $defaultValue;
    }

    /**
     * Get application init params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Retrieve directories
     *
     * @return \Magento\App\Dir
     */
    public function getDirectories()
    {
        return $this->_dir;
    }

    /**
     * Reinitialize primary configuration
     */
    public function reinit()
    {
        $this->loadString('<config/>');
        $this->_loader->load($this);
    }

    /**
     * Retrieve class definition config
     *
     * @return string
     */
    public function getDefinitionPath()
    {
        $pathInfo = (array) $this->getNode('global/di/definitions');
        if (isset($pathInfo['path'])) {
            return $pathInfo['path'];
        } else if (isset($pathInfo['relativePath'])) {
            return $this->_dir->getDir(\Magento\App\Dir::ROOT) . DIRECTORY_SEPARATOR . $pathInfo['relativePath'];
        } else {
            return $this->_dir->getDir(\Magento\App\Dir::DI);
        }
    }

    /**
     * Retrieve definition format
     *
     * @return string
     */
    public function getDefinitionFormat()
    {
        return (string) $this->getNode('global/di/definitions/format');
    }

    /**
     * Configure object manager
     *
     * \Magento\Core\Model\ObjectManager $objectManager
     */
    public function configure(\Magento\Core\Model\ObjectManager $objectManager)
    {
        \Magento\Profiler::start('initial');

        $objectManager->configure(array(
            'Magento\Core\Model\Config\Loader\Local' => array(
                'parameters' => array(
                    'configDirectory' => $this->_dir->getDir(\Magento\App\Dir::CONFIG),
                )
            ),
            'Magento\Core\Model\Cache\Frontend\Factory' => array(
                'parameters' => array(
                    'decorators' => $this->_getCacheFrontendDecorators(),
                )
            ),
        ));

        \Magento\Profiler::stop('initial');
    }

    /**
     * Retrieve cache frontend decorators configuration
     *
     * @return array
     */
    protected function _getCacheFrontendDecorators()
    {
        $result = array();
        // mark all cache entries with a special tag to be able to clean only cache belonging to the application
        $result[] = array(
            'class' => 'Magento\Cache\Frontend\Decorator\TagScope',
            'parameters' => array('tag' => 'MAGE'),
        );
        if (\Magento\Profiler::isEnabled()) {
            $result[] = array(
                'class' => 'Magento\Cache\Frontend\Decorator\Profiler',
                'parameters' => array('backendPrefixes' => array('Zend_Cache_Backend_', 'Magento\Cache\Backend\\')),
            );
        }
        return $result;
    }
}
