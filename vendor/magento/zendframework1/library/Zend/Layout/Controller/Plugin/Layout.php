<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Plugin_Abstract */
#require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Render layouts
 *
 * @uses       Zend_Controller_Plugin_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Layout_Controller_Plugin_Layout extends Zend_Controller_Plugin_Abstract
{
    protected $_layoutActionHelper = null;

    /**
     * @var Zend_Layout
     */
    protected $_layout;

    /**
     * Constructor
     *
     * @param  Zend_Layout $layout
     * @return void
     */
    public function __construct(Zend_Layout $layout = null)
    {
        if (null !== $layout) {
            $this->setLayout($layout);
        }
    }

    /**
     * Retrieve layout object
     *
     * @return Zend_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Set layout object
     *
     * @param  Zend_Layout $layout
     * @return Zend_Layout_Controller_Plugin_Layout
     */
    public function setLayout(Zend_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Set layout action helper
     *
     * @param  Zend_Layout_Controller_Action_Helper_Layout $layoutActionHelper
     * @return Zend_Layout_Controller_Plugin_Layout
     */
    public function setLayoutActionHelper(Zend_Layout_Controller_Action_Helper_Layout $layoutActionHelper)
    {
        $this->_layoutActionHelper = $layoutActionHelper;
        return $this;
    }

    /**
     * Retrieve layout action helper
     *
     * @return Zend_Layout_Controller_Action_Helper_Layout
     */
    public function getLayoutActionHelper()
    {
        return $this->_layoutActionHelper;
    }

    /**
     * postDispatch() plugin hook -- render layout
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = $this->getLayout();
        $helper = $this->getLayoutActionHelper();

        // Return early if forward detected
        if (!$request->isDispatched()
            || $this->getResponse()->isRedirect()
            || ($layout->getMvcSuccessfulActionOnly()
                && (!empty($helper) && !$helper->isActionControllerSuccessful())))
        {
            return;
        }

        // Return early if layout has been disabled
        if (!$layout->isEnabled()) {
            return;
        }

        $response   = $this->getResponse();
        $content    = $response->getBody(true);
        $contentKey = $layout->getContentKey();

        if (isset($content['default'])) {
            $content[$contentKey] = $content['default'];
        }
        if ('default' != $contentKey) {
            unset($content['default']);
        }

        $layout->assign($content);

        $fullContent = null;
        $obStartLevel = ob_get_level();
        try {
            $fullContent = $layout->render();
            $response->setBody($fullContent);
        } catch (Exception $e) {
            while (ob_get_level() > $obStartLevel) {
                $fullContent .= ob_get_clean();
            }
            $request->setParam('layoutFullContent', $fullContent);
            $request->setParam('layoutContent', $layout->content);
            $response->setBody(null);
            throw $e;
        }

    }
}
