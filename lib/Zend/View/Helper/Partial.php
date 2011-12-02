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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Partial.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for rendering a template fragment in its own variable scope.
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Partial extends Zend_View_Helper_Abstract
{
    /**
     * Variable to which object will be assigned
     * @var string
     */
    protected $_objectKey;

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are passed, returns the helper instance.
     *
     * If the $model is an array, it is passed to the view object's assign()
     * method.
     *
     * If the $model is an object, it first checks to see if the object
     * implements a 'toArray' method; if so, it passes the result of that
     * method to to the view object's assign() method. Otherwise, the result of
     * get_object_vars() is passed.
     *
     * @param  string $name Name of view script
     * @param  string|array $module If $model is empty, and $module is an array,
     *                              these are the variables to populate in the
     *                              view. Otherwise, the module in which the
     *                              partial resides
     * @param  array $model Variables to populate in the view
     * @return string|Zend_View_Helper_Partial
     */
    public function partial($name = null, $module = null, $model = null)
    {
        if (0 == func_num_args()) {
            return $this;
        }

        $view = $this->cloneView();
        if (isset($this->partialCounter)) {
            $view->partialCounter = $this->partialCounter;
        }
        if ((null !== $module) && is_string($module)) {
            #require_once 'Zend/Controller/Front.php';
            $moduleDir = Zend_Controller_Front::getInstance()->getControllerDirectory($module);
            if (null === $moduleDir) {
                #require_once 'Zend/View/Helper/Partial/Exception.php';
                $e = new Zend_View_Helper_Partial_Exception('Cannot render partial; module does not exist');
                $e->setView($this->view);
                throw $e;
            }
            $viewsDir = dirname($moduleDir) . '/views';
            $view->addBasePath($viewsDir);
        } elseif ((null == $model) && (null !== $module)
            && (is_array($module) || is_object($module)))
        {
            $model = $module;
        }

        if (!empty($model)) {
            if (is_array($model)) {
                $view->assign($model);
            } elseif (is_object($model)) {
                if (null !== ($objectKey = $this->getObjectKey())) {
                    $view->assign($objectKey, $model);
                } elseif (method_exists($model, 'toArray')) {
                    $view->assign($model->toArray());
                } else {
                    $view->assign(get_object_vars($model));
                }
            }
        }

        return $view->render($name);
    }

    /**
     * Clone the current View
     *
     * @return Zend_View_Interface
     */
    public function cloneView()
    {
        $view = clone $this->view;
        $view->clearVars();
        return $view;
    }

    /**
     * Set object key
     *
     * @param  string $key
     * @return Zend_View_Helper_Partial
     */
    public function setObjectKey($key)
    {
        if (null === $key) {
            $this->_objectKey = null;
        } else {
            $this->_objectKey = (string) $key;
        }

        return $this;
    }

    /**
     * Retrieve object key
     *
     * The objectKey is the variable to which an object in the iterator will be
     * assigned.
     *
     * @return null|string
     */
    public function getObjectKey()
    {
        return $this->_objectKey;
    }
}
