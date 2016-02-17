<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Model;

use Traversable;
use Zend\Json\Json;
use Zend\Stdlib\ArrayUtils;

class JsonModel extends ViewModel
{
    /**
     * JSON probably won't need to be captured into a
     * a parent container by default.
     *
     * @var string
     */
    protected $captureTo = null;

    /**
     * JSONP callback (if set, wraps the return in a function call)
     *
     * @var string
     */
    protected $jsonpCallback = null;

    /**
     * JSON is usually terminal
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * Set the JSONP callback function name
     *
     * @param  string $callback
     * @return JsonModel
     */
    public function setJsonpCallback($callback)
    {
        $this->jsonpCallback = $callback;
        return $this;
    }

    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $variables = $this->getVariables();
        if ($variables instanceof Traversable) {
            $variables = ArrayUtils::iteratorToArray($variables);
        }

        $options = array(
            'prettyPrint' => $this->getOption('prettyPrint'),
        );

        if (null !== $this->jsonpCallback) {
            return $this->jsonpCallback.'('.Json::encode($variables, false, $options).');';
        }
        return Json::encode($variables, false, $options);
    }
}
