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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_App_Options
{
    /**@+
     * Application option names
     */
    const OPTION_APP_RUN_CODE            = 'MAGE_RUN_CODE';
    const OPTION_APP_RUN_TYPE            = 'MAGE_RUN_TYPE';
    const OPTION_LOCAL_CONFIG_EXTRA_FILE = 'MAGE_LOCAL_CONFIG';
    /**@-*/

    /**@+
     * Supported application run types
     */
    const APP_RUN_TYPE_STORE    = 'store';
    const APP_RUN_TYPE_GROUP    = 'group';
    const APP_RUN_TYPE_WEBSITE  = 'website';
    /**@-*/

    /**
     * Shorthand for the list of supported application run types
     *
     * @var array
     */
    protected $_supportedRunTypes = array(
        self::APP_RUN_TYPE_STORE, self::APP_RUN_TYPE_GROUP, self::APP_RUN_TYPE_WEBSITE
    );

    /**
     * Store or website code
     *
     * @var string
     */
    protected $_runCode = '';

    /**
     * Run store or run website
     *
     * @var string
     */
    protected $_runType = self::APP_RUN_TYPE_STORE;

    /**
     * Application run options
     *
     * @var array
     */
    protected $_runOptions = array();

    /**
     * Constructor
     *
     * @param array $options Source of option values
     * @throws InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (isset($options[self::OPTION_APP_RUN_CODE])) {
            $this->_runCode = $options[self::OPTION_APP_RUN_CODE];
        }

        if (isset($options[self::OPTION_APP_RUN_TYPE])) {
            $this->_runType = $options[self::OPTION_APP_RUN_TYPE];
            if (!in_array($this->_runType, $this->_supportedRunTypes)) {
                throw new InvalidArgumentException(sprintf(
                    'Application run type "%s" is not recognized, supported values: "%s".',
                    $this->_runType,
                    implode('", "', $this->_supportedRunTypes)
                ));
            }
        }

        if (!empty($options[self::OPTION_LOCAL_CONFIG_EXTRA_FILE])) {
            $localConfigFile = $options[self::OPTION_LOCAL_CONFIG_EXTRA_FILE];
            $this->_runOptions[Mage_Core_Model_Config::OPTION_LOCAL_CONFIG_EXTRA_FILE] = $localConfigFile;
        }
    }

    /**
     * Retrieve application run code
     *
     * @return string
     */
    public function getRunCode()
    {
        return $this->_runCode;
    }

    /**
     * Retrieve application run type
     *
     * @return string
     */
    public function getRunType()
    {
        return $this->_runType;
    }

    /**
     * Retrieve application run options
     *
     * @return array
     */
    public function getRunOptions()
    {
        return $this->_runOptions;
    }
}
