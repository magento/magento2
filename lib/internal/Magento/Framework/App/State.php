<?php
/**
 *  Application state flags
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
namespace Magento\Framework\App;

class State
{
    /**
     * Application run code
     */
    const PARAM_MODE = 'MAGE_MODE';

    /**
     * Application mode
     *
     * @var string
     */
    private $_appMode;

    /**
     * Is downloader flag
     *
     * @var bool
     */
    protected $_isDownloader = false;

    /**
     * Update mode flag
     *
     * @var bool
     */
    protected $_updateMode = false;

    /**
     * Application install date
     *
     * @var string
     */
    protected $_installDate;

    /**
     * Config scope model
     *
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * Area code
     *
     * @var string
     */
    protected $_areaCode;

    /**#@+
     * Application modes
     */
    const MODE_DEVELOPER = 'developer';

    const MODE_PRODUCTION = 'production';

    const MODE_DEFAULT = 'default';

    /**#@-*/
    const PARAM_INSTALL_DATE = 'install.date';

    /**
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param string $installDate
     * @param string $mode
     * @throws \LogicException
     */
    public function __construct(
        \Magento\Framework\Config\ScopeInterface $configScope,
        $installDate,
        $mode = self::MODE_DEFAULT
    ) {
        $this->_installDate = strtotime((string)$installDate);
        $this->_configScope = $configScope;
        switch ($mode) {
            case self::MODE_DEVELOPER:
            case self::MODE_PRODUCTION:
            case self::MODE_DEFAULT:
                $this->_appMode = $mode;
                break;
            default:
                throw new \InvalidArgumentException("Unknown application mode: {$mode}");
        }
    }

    /**
     * Check if application is installed
     *
     * @return bool
     */
    public function isInstalled()
    {
        return (bool)$this->_installDate;
    }

    /**
     * Return current app mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_appMode;
    }

    /**
     * Set update mode flag
     *
     * @param bool $value
     * @return void
     */
    public function setUpdateMode($value)
    {
        $this->_updateMode = $value;
    }

    /**
     * Get update mode flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUpdateMode()
    {
        return $this->_updateMode;
    }

    /**
     * Set is downloader flag
     *
     * @param bool $flag
     * @return void
     */
    public function setIsDownloader($flag = true)
    {
        $this->_isDownloader = $flag;
    }

    /**
     * Set install date
     *
     * @param string $date
     * @return void
     */
    public function setInstallDate($date)
    {
        $this->_installDate = $date;
    }

    /**
     * Get install date
     *
     * @return int
     */
    public function getInstallDate()
    {
        return $this->_installDate;
    }

    /**
     * Set area code
     *
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception
     */
    public function setAreaCode($code)
    {
        if (isset($this->_areaCode)) {
            throw new \Magento\Framework\Exception('Area code is already set');
        }
        $this->_configScope->setCurrentScope($code);
        $this->_areaCode = $code;
    }

    /**
     * Get area code
     *
     * @return string
     * @throws \Magento\Framework\Exception
     */
    public function getAreaCode()
    {
        if (!isset($this->_areaCode)) {
            throw new \Magento\Framework\Exception('Area code is not set');
        }
        return $this->_areaCode;
    }

    /**
     * Emulate callback inside some area code
     *
     * @param string $areaCode
     * @param callable $callback
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function emulateAreaCode($areaCode, $callback, $params = array())
    {
        $currentArea = $this->_areaCode;
        $this->_areaCode = $areaCode;
        try {
            $result = call_user_func_array($callback, $params);
        } catch (\Exception $e) {
            $this->_areaCode = $currentArea;
            throw $e;
        }
        $this->_areaCode = $currentArea;
        return $result;
    }
}
