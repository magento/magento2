<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Design Editor main helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Parameter to indicate the translation mode (null, text, script, or alt).
     */
    const TRANSLATION_MODE = "translation_mode";

    /**
     * XML path to VDE front name setting
     *
     * @var string
     */
    protected $_frontName;

    /**
     * XML path to VDE disabled cache type setting
     *
     * @var array
     */
    protected $_disabledCacheTypes;

    /**
     * @var string
     */
    protected $_translationMode;

    /**
     * @param Context $context
     * @param string $frontName
     * @param array $disabledCacheTypes
     */
    public function __construct(Context $context, $frontName, array $disabledCacheTypes = [])
    {
        parent::__construct($context);
        $this->_frontName = $frontName;
        $this->_disabledCacheTypes = $disabledCacheTypes;
    }

    /**
     * Get VDE front name prefix
     *
     * @return string
     */
    public function getFrontName()
    {
        return $this->_frontName;
    }

    /**
     * Get disabled cache types in VDE mode
     *
     * @return array
     */
    public function getDisabledCacheTypes()
    {
        return $this->_disabledCacheTypes;
    }

    /**
     * Returns the translation mode the current request is in (null, text, script, or alt).
     *
     * @return string|null
     */
    public function getTranslationMode()
    {
        return $this->_translationMode;
    }

    /**
     * Sets the translation mode for the current request (null, text, script, or alt);
     *
     * @param RequestInterface $request
     * @return $this
     */
    public function setTranslationMode(RequestInterface $request)
    {
        $this->_translationMode = $request->getParam(self::TRANSLATION_MODE, null);
        return $this;
    }

    /**
     * Returns an indicator of whether or not inline translation is allowed in VDE.
     *
     * @return bool
     */
    public function isAllowed()
    {
        return $this->_translationMode !== null;
    }

    /**
     * This method returns an indicator of whether or not the current request is for vde
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function isVdeRequest(RequestInterface $request = null)
    {
        $result = false;
        if (null !== $request) {
            $splitPath = explode('/', trim($request->getOriginalPathInfo(), '/'));
            if (count($splitPath) >= 3) {
                list($frontName, $currentMode, $themeId) = $splitPath;
                $result = $frontName === $this->_frontName && in_array(
                    $currentMode,
                    [\Magento\DesignEditor\Model\State::MODE_NAVIGATION]
                ) && is_numeric(
                    $themeId
                );
            }
        }
        return $result;
    }
}
