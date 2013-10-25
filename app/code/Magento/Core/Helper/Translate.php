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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core translate helper
 */
namespace Magento\Core\Helper;

class Translate extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * Design package instance
     *
     * @var \Magento\View\DesignInterface
     */
    protected $_design;
    
    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @param Context $context
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\View\DesignInterface $design,
        \Magento\Event\ManagerInterface $eventManager
    ) {
        $this->_eventManager = $eventManager;
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

            $this->_translator->processAjaxPost($translate);
            $result = $returnType == 'json' ? "{success:true}" : true;
        } catch (\Exception $e) {
            $result = $returnType == 'json' ? "{error:true,message:'" . $e->getMessage() . "'}" : false;
        }
        return $result;
    }

    /**
     * This method initializes the Translate object for this instance.
     * @param $localeCode string
     * @param $area string
     * @param $forceReload bool
     * @return \Magento\Core\Model\Translate
     */
    public function initTranslate($localeCode, $area, $forceReload)
    {
        $this->_translator->setLocale($localeCode);

        $dispatchResult = new \Magento\Object(array(
            'inline_type' => null,
            'params' => array('area' => $area)
        ));
        $this->_eventManager->dispatch('translate_initialization_before', array(
            'translate_object' => $this->_translator,
            'result' => $dispatchResult
        ));
        $this->_translator->init($area, $dispatchResult, $forceReload);
        return $this;
    }
}
