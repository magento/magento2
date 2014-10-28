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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Model;

use Magento\Config\Config;
use Magento\Config\ConfigFactory;

/**
 * License model
 *
 * @package Magento\Setup\Model
 */
class License
{
    /**
     * License File location
     *
     * @var string
     */
    const LICENSE_FILENAME = 'LICENSE.txt';

    /**
     * Path of license file
     *
     * @var string
     */
    protected $licenseFile;

    /**
     * Configuration details
     *
     * @var Config
     */
    protected $config;

    /**
     * ConfigFactory to create config
     *
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * Constructor
     *
     * @param ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
        $this->config = $this->configFactory->create();
        $this->licenseFile = $this->config->getMagentoBasePath() . DIRECTORY_SEPARATOR . self::LICENSE_FILENAME;
    }

    /**
     * Returns contents of License file.
     *
     * @return string
     */
    public function getContents()
    {
        if (!file_exists($this->licenseFile)) {
            return false;
        }
        return file_get_contents($this->licenseFile);
    }
}
