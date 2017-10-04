<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Application state flags.
 * Can be used to retrieve current application mode and current area.
 *
 * Note: Area code communication and emulation will be removed from this class.
 *
 * @api
 */
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
    protected $_appMode;

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

    /**
     * Is area code being emulated
     *
     * @var bool
     */
    protected $_isAreaCodeEmulated = false;

    /**
     * @var AreaList
     */
    private $areaList;

    /**#@+
     * Application modes
     */
    const MODE_DEVELOPER = 'developer';

    const MODE_PRODUCTION = 'production';

    const MODE_DEFAULT = 'default';

    /**#@-*/

    /**
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param string $mode
     * @throws \LogicException
     */
    public function __construct(
        \Magento\Framework\Config\ScopeInterface $configScope,
        $mode = self::MODE_DEFAULT
    ) {
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
     * Return current app mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_appMode;
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
     * Set area code
     *
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setAreaCode($code)
    {
        $this->checkAreaCode($code);

        if (isset($this->_areaCode)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Area code is already set')
            );
        }
        $this->_configScope->setCurrentScope($code);
        $this->_areaCode = $code;
    }

    /**
     * Get area code
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAreaCode()
    {
        if (!isset($this->_areaCode)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Area code is not set')
            );
        }
        return $this->_areaCode;
    }

    /**
     * Checks whether area code is being emulated
     *
     * @return bool
     */
    public function isAreaCodeEmulated()
    {
        return $this->_isAreaCodeEmulated;
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
    public function emulateAreaCode($areaCode, $callback, $params = [])
    {
        $this->checkAreaCode($areaCode);

        $currentArea = $this->_areaCode;
        $this->_areaCode = $areaCode;
        $this->_isAreaCodeEmulated = true;
        try {
            $result = call_user_func_array($callback, $params);
        } catch (\Exception $e) {
            $this->_areaCode = $currentArea;
            $this->_isAreaCodeEmulated = false;
            throw $e;
        }
        $this->_areaCode = $currentArea;
        $this->_isAreaCodeEmulated = false;
        return $result;
    }

    /**
     * Check that area code exists
     *
     * @param string $areaCode
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function checkAreaCode($areaCode)
    {
        $areaCodes = array_merge(
            [Area::AREA_GLOBAL],
            $this->getAreaListInstance()->getCodes()
        );

        if (!in_array($areaCode, $areaCodes)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Area code "%1" does not exist', [$areaCode])
            );
        }
    }

    /**
     * Get Instance of AreaList
     *
     * @return AreaList
     * @deprecated 100.2.0
     */
    private function getAreaListInstance()
    {
        if ($this->areaList === null) {
            $this->areaList = ObjectManager::getInstance()->get(AreaList::class);
        }

        return $this->areaList;
    }
}
