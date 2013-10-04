<?php
/**
 * Application config loader
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

class Loader implements \Magento\Core\Model\Config\LoaderInterface
{
    /**
     * Primary application configuration
     *
     * @var \Magento\Core\Model\Config\Primary
     */
    protected $_primaryConfig;

    /**
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_fileReader;

    /**
     * @param \Magento\Core\Model\Config\Primary $primaryConfig
     * @param \Magento\Core\Model\Config\Modules\Reader $fileReader
     */
    public function __construct(
        \Magento\Core\Model\Config\Primary $primaryConfig,
        \Magento\Core\Model\Config\Modules\Reader $fileReader
    ) {
        $this->_primaryConfig = $primaryConfig;
        $this->_fileReader = $fileReader;
    }

    /**
     * Populate configuration object
     *
     * @param \Magento\Core\Model\Config\Base $config
     */
    public function load(\Magento\Core\Model\Config\Base $config)
    {
        if (!$config->getNode()) {
            $config->loadString('<config></config>');
        }

        \Magento\Profiler::start('config');
        \Magento\Profiler::start('load_modules');

        $config->extend($this->_primaryConfig);

        \Magento\Profiler::start('load_modules_configuration');

        $this->_fileReader->loadModulesConfiguration(array('config.xml'), $config);
        \Magento\Profiler::stop('load_modules_configuration');

        $config->applyExtends();

        \Magento\Profiler::stop('load_modules');
        \Magento\Profiler::stop('config');
    }
}
