<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

/**
 * Helper for declaring default values of template variables
 */
class DeclareVars extends AbstractHelper
{
    /**
     * The view object that created this helper object.
     *
     * @var \Zend\View\View
     */
    public $view;

    /**
     * Declare template vars to set default values and avoid notices when using strictVars
     *
     * Primarily for use when using {@link Zend\View\Variables::setStrictVars()},
     * this helper can be used to declare template variables that may or may
     * not already be set in the view object, as well as to set default values.
     * Arrays passed as arguments to the method will be used to set default
     * values; otherwise, if the variable does not exist, it is set to an empty
     * string.
     *
     * Usage:
     * <code>
     * $this->declareVars(
     *     'varName1',
     *     'varName2',
     *     array('varName3' => 'defaultValue',
     *           'varName4' => array()
     *     )
     * );
     * </code>
     *
     * @param string|array variable number of arguments, all string names of variables to test
     * @return void
     */
    public function __invoke()
    {
        $view = $this->getView();
        $args = func_get_args();
        foreach ($args as $key) {
            if (is_array($key)) {
                foreach ($key as $name => $value) {
                    $this->declareVar($name, $value);
                }
            } elseif (!isset($view->vars()->$key)) {
                $this->declareVar($key);
            }
        }
    }

    /**
     * Set a view variable
     *
     * Checks to see if a $key is set in the view object; if not, sets it to $value.
     *
     * @param  string $key
     * @param  string $value Defaults to an empty string
     * @return void
     */
    protected function declareVar($key, $value = '')
    {
        $view = $this->getView();
        $vars = $view->vars();
        if (!isset($vars->$key)) {
            $vars->$key = $value;
        }
    }
}
