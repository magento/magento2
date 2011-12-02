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
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
#require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Create and send autocompletion lists
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Controller_Action_Helper_AutoComplete_Abstract extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Suppress exit when sendJson() called
     *
     * @var boolean
     */
    public $suppressExit = false;

    /**
     * Validate autocompletion data
     *
     * @param  mixed $data
     * @return boolean
     */
    abstract public function validateData($data);

    /**
     * Prepare autocompletion data
     *
     * @param  mixed   $data
     * @param  boolean $keepLayouts
     * @return mixed
     */
    abstract public function prepareAutoCompletion($data, $keepLayouts = false);

    /**
     * Disable layouts and view renderer
     *
     * @return Zend_Controller_Action_Helper_AutoComplete_Abstract Provides a fluent interface
     */
    public function disableLayouts()
    {
        /**
         * @see Zend_Layout
         */
        #require_once 'Zend/Layout.php';
        if (null !== ($layout = Zend_Layout::getMvcInstance())) {
            $layout->disableLayout();
        }

        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);

        return $this;
    }

    /**
     * Encode data to JSON
     *
     * @param  mixed $data
     * @param  bool  $keepLayouts
     * @throws Zend_Controller_Action_Exception
     * @return string
     */
    public function encodeJson($data, $keepLayouts = false)
    {
        if ($this->validateData($data)) {
            return Zend_Controller_Action_HelperBroker::getStaticHelper('Json')->encodeJson($data, $keepLayouts);
        }

        /**
         * @see Zend_Controller_Action_Exception
         */
        #require_once 'Zend/Controller/Action/Exception.php';
        throw new Zend_Controller_Action_Exception('Invalid data passed for autocompletion');
    }

    /**
     * Send autocompletion data
     *
     * Calls prepareAutoCompletion, populates response body with this
     * information, and sends response.
     *
     * @param  mixed $data
     * @param  bool  $keepLayouts
     * @return string|void
     */
    public function sendAutoCompletion($data, $keepLayouts = false)
    {
        $data = $this->prepareAutoCompletion($data, $keepLayouts);

        $response = $this->getResponse();
        $response->setBody($data);

        if (!$this->suppressExit) {
            $response->sendResponse();
            exit;
        }

        return $data;
    }

    /**
     * Strategy pattern: allow calling helper as broker method
     *
     * Prepares autocompletion data and, if $sendNow is true, immediately sends
     * response.
     *
     * @param  mixed $data
     * @param  bool  $sendNow
     * @param  bool  $keepLayouts
     * @return string|void
     */
    public function direct($data, $sendNow = true, $keepLayouts = false)
    {
        if ($sendNow) {
            return $this->sendAutoCompletion($data, $keepLayouts);
        }

        return $this->prepareAutoCompletion($data, $keepLayouts);
    }
}
