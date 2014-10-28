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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PaginationControl.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_PaginationControl
{
    /**
     * View instance
     *
     * @var Zend_View_Instance
     */
    public $view = null;

    /**
     * Default view partial
     *
     * @var string|array
     */
    protected static $_defaultViewPartial = null;

    /**
     * Sets the view instance.
     *
     * @param  Zend_View_Interface $view View instance
     * @return Zend_View_Helper_PaginationControl
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Sets the default view partial.
     *
     * @param string|array $partial View partial
     */
    public static function setDefaultViewPartial($partial)
    {
        self::$_defaultViewPartial = $partial;
    }

    /**
     * Gets the default view partial
     *
     * @return string|array
     */
    public static function getDefaultViewPartial()
    {
        return self::$_defaultViewPartial;
    }

    /**
     * Render the provided pages.  This checks if $view->paginator is set and,
     * if so, uses that.  Also, if no scrolling style or partial are specified,
     * the defaults will be used (if set).
     *
     * @param  Zend_Paginator (Optional) $paginator
     * @param  string $scrollingStyle (Optional) Scrolling style
     * @param  string $partial (Optional) View partial
     * @param  array|string $params (Optional) params to pass to the partial
     * @return string
     * @throws Zend_View_Exception
     */
    public function paginationControl(Zend_Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
    {
        if ($paginator === null) {
            if (isset($this->view->paginator) and $this->view->paginator !== null and $this->view->paginator instanceof Zend_Paginator) {
                $paginator = $this->view->paginator;
            } else {
                /**
                 * @see Zend_View_Exception
                 */
                #require_once 'Zend/View/Exception.php';

                $e = new Zend_View_Exception('No paginator instance provided or incorrect type');
                $e->setView($this->view);
                throw $e;
            }
        }

        if ($partial === null) {
            if (self::$_defaultViewPartial === null) {
                /**
                 * @see Zend_View_Exception
                 */
                #require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception('No view partial provided and no default set');
                $e->setView($this->view);
                throw $e;
            }

            $partial = self::$_defaultViewPartial;
        }

        $pages = get_object_vars($paginator->getPages($scrollingStyle));

        if ($params !== null) {
            $pages = array_merge($pages, (array) $params);
        }

        if (is_array($partial)) {
            if (count($partial) != 2) {
                /**
                 * @see Zend_View_Exception
                 */
                #require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception('A view partial supplied as an array must contain two values: the filename and its module');
                $e->setView($this->view);
                throw $e;
            }

            if ($partial[1] !== null) {
                return $this->view->partial($partial[0], $partial[1], $pages);
            }

            $partial = $partial[0];
        }

        return $this->view->partial($partial, $pages);
    }
}
