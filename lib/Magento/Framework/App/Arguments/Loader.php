<?php
/**
 * Local Application configuration loader (app/etc/local.xml)
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Arguments;

class Loader
{
    /**
     * Local configuration file
     */
    const PARAM_CUSTOM_FILE = 'custom.options.file';

    /**
     * Local configuration file
     */
    const LOCAL_CONFIG_FILE = 'local.xml';

    /**
     * Directory registry
     *
     * @var string
     */
    protected $_dir;

    /**
     * Custom config file
     *
     * @var string
     */
    protected $_customFile;

    /**
     * Configuration identifier attributes
     *
     * @var array
     */
    protected $_idAttributes = array(
        '/config/resource' => 'name',
        '/config/connection' => 'name',
        '/config/cache/frontend' => 'name',
        '/config/cache/type' => 'name'
    );

    /**
     * @param \Magento\Framework\App\Filesystem\DirectoryList $dirList
     * @param string $customFile
     */
    public function __construct(\Magento\Framework\App\Filesystem\DirectoryList $dirList, $customFile = null)
    {
        $this->_dir = $dirList->getDir(\Magento\Framework\App\Filesystem::CONFIG_DIR);
        $this->_customFile = $customFile;
    }

    /**
     * Load configuration
     *
     * @return array
     */
    public function load()
    {
        $localConfig = new \Magento\Framework\Config\Dom('<config/>', $this->_idAttributes);

        $localConfigFile = $this->_dir . '/' . self::LOCAL_CONFIG_FILE;
        if (file_exists($localConfigFile)) {
            // 1. app/etc/local.xml
            $localConfig->merge(file_get_contents($localConfigFile));

            // 2. app/etc/<dir>/<file>.xml
            if (preg_match('/^[a-z\d_-]+(\/|\\\)+[a-z\d_-]+\.xml$/', $this->_customFile)) {
                $localConfigExtraFile = $this->_dir . '/' . $this->_customFile;
                $localConfig->merge(file_get_contents($localConfigExtraFile));
            }
        }

        $arrayNodeConfig = new \Magento\Framework\Config\Dom\ArrayNodeConfig(
            new \Magento\Framework\Config\Dom\NodePathMatcher(),
            $this->_idAttributes
        );
        $converter = new \Magento\Framework\Config\Converter\Dom\Flat($arrayNodeConfig);

        $result = $converter->convert($localConfig->getDom());
        return !empty($result['config']) ? $result['config'] : array();
    }
}
