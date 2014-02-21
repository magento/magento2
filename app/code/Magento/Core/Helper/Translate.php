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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core translate helper
 */
namespace Magento\Core\Helper;

class Translate extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Design package instance
     *
     * @var \Magento\View\DesignInterface
     */
    protected $_design;

    /**
     * Inline translate
     *
     * @var \Magento\Translate\Inline\ParserInterface
     */
    protected $_inlineParser;

    /**
     * Translate library
     *
     * @var \Magento\Translate
     */
    protected $_translator;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Translate\Inline\ParserInterface $inlineParser
     * @param \Magento\Translate $translate
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\View\DesignInterface $design,
        \Magento\Translate\Inline\ParserInterface $inlineParser,
        \Magento\Translate $translate
    ) {
        $this->_design = $design;
        $this->_inlineParser = $inlineParser;
        $this->_translator = $translate;
        parent::__construct($context);
    }

    /**
     * Save translation data to database for specific area
     *
     * @param array $translate
     * @param string $area
     * @param string $returnType
     * @return string
     */
    public function apply($translate, $area, $returnType = 'json')
    {
        try {
            if ($area) {
                $this->_design->setArea($area);
            }

            $this->_inlineParser->processAjaxPost($translate);
            $result = $returnType == 'json' ? "{success:true}" : true;
        } catch (\Exception $e) {
            $result = $returnType == 'json' ? "{error:true,message:'" . $e->getMessage() . "'}" : false;
        }
        return $result;
    }

    /**
     * This method initializes the Translate object for this instance.
     *
     * @param string $localeCode
     * @param bool $forceReload
     * @param null $area
     * @return $this
     */
    public function initTranslate($localeCode, $forceReload, $area = null)
    {
        $this->_translator->setLocale($localeCode);

        $dispatchResult = new \Magento\Object(array(
            'inline_type' => null
        ));
        $this->_eventManager->dispatch('translate_initialization_before', array(
            'translate_object' => $this->_translator,
            'result' => $dispatchResult
        ));
        $area = isset($area) ? $area : $this->_design->getArea();
        $this->_translator->init($area, $dispatchResult, $forceReload);
        return $this;
    }
}
