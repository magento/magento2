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
 * @version    $Id: PartialLoop.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Partial */
#require_once 'Zend/View/Helper/Partial.php';

/**
 * Helper for rendering a template fragment in its own variable scope; iterates
 * over data provided and renders for each iteration.
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_PartialLoop extends Zend_View_Helper_Partial
{

    /**
     * Marker to where the pointer is at in the loop
     * @var integer
     */
    protected $partialCounter = 0;

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @param  string $name Name of view script
     * @param  string|array $module If $model is empty, and $module is an array,
     *                              these are the variables to populate in the
     *                              view. Otherwise, the module in which the
     *                              partial resides
     * @param  array $model Variables to populate in the view
     * @return string
     */
    public function partialLoop($name = null, $module = null, $model = null)
    {
        if (0 == func_num_args()) {
            return $this;
        }

        if ((null === $model) && (null !== $module)) {
            $model  = $module;
            $module = null;
        }

        if (!is_array($model)
            && (!$model instanceof Traversable)
            && (is_object($model) && !method_exists($model, 'toArray'))
        ) {
            #require_once 'Zend/View/Helper/Partial/Exception.php';
            $e = new Zend_View_Helper_Partial_Exception('PartialLoop helper requires iterable data');
            $e->setView($this->view);
            throw $e;
        }

        if (is_object($model)
            && (!$model instanceof Traversable)
            && method_exists($model, 'toArray')
        ) {
            $model = $model->toArray();
        }

        $content = '';
        // reset the counter if it's call again
        $this->partialCounter = 0;
        foreach ($model as $item) {
            // increment the counter variable
            $this->partialCounter++;

            $content .= $this->partial($name, $module, $item);
        }

        return $content;
    }
}
