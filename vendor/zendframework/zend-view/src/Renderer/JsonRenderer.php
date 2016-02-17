<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Renderer;

use JsonSerializable;
use Traversable;
use Zend\Json\Json;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\ResolverInterface as Resolver;

/**
 * JSON renderer
 */
class JsonRenderer implements Renderer, TreeRendererInterface
{
    /**
     * Whether or not to merge child models with no capture-to value set
     * @var bool
     */
    protected $mergeUnnamedChildren = false;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * JSONP callback (if set, wraps the return in a function call)
     *
     * @var string
     */
    protected $jsonpCallback = null;

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return mixed
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @todo   Determine use case for resolvers when rendering JSON
     * @param  Resolver $resolver
     * @return Renderer
     */
    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Set flag indicating whether or not to merge unnamed children
     *
     * @param  bool $mergeUnnamedChildren
     * @return JsonRenderer
     */
    public function setMergeUnnamedChildren($mergeUnnamedChildren)
    {
        $this->mergeUnnamedChildren = (bool) $mergeUnnamedChildren;
        return $this;
    }

    /**
     * Set the JSONP callback function name
     *
     * @param  string $callback
     * @return JsonRenderer
     */
    public function setJsonpCallback($callback)
    {
        $callback = (string) $callback;
        if (!empty($callback)) {
            $this->jsonpCallback = $callback;
        }
        return $this;
    }

    /**
     * Returns whether or not the jsonpCallback has been set
     *
     * @return bool
     */
    public function hasJsonpCallback()
    {
        return (null !== $this->jsonpCallback);
    }

    /**
     * Should we merge unnamed children?
     *
     * @return bool
     */
    public function mergeUnnamedChildren()
    {
        return $this->mergeUnnamedChildren;
    }

    /**
     * Renders values as JSON
     *
     * @todo   Determine what use case exists for accepting both $nameOrModel and $values
     * @param  string|Model $nameOrModel The script/resource process, or a view model
     * @param  null|array|\ArrayAccess $values Values to use during rendering
     * @throws Exception\DomainException
     * @return string The script output.
     */
    public function render($nameOrModel, $values = null)
    {
        // use case 1: View Models
        // Serialize variables in view model
        if ($nameOrModel instanceof Model) {
            if ($nameOrModel instanceof JsonModel) {
                $children = $this->recurseModel($nameOrModel, false);
                $this->injectChildren($nameOrModel, $children);
                $values = $nameOrModel->serialize();
            } else {
                $values = $this->recurseModel($nameOrModel);
                $values = Json::encode($values);
            }

            if ($this->hasJsonpCallback()) {
                $values = $this->jsonpCallback . '(' . $values . ');';
            }
            return $values;
        }

        // use case 2: $nameOrModel is populated, $values is not
        // Serialize $nameOrModel
        if (null === $values) {
            if (!is_object($nameOrModel) || $nameOrModel instanceof JsonSerializable) {
                $return = Json::encode($nameOrModel);
            } elseif ($nameOrModel instanceof Traversable) {
                $nameOrModel = ArrayUtils::iteratorToArray($nameOrModel);
                $return = Json::encode($nameOrModel);
            } else {
                $return = Json::encode(get_object_vars($nameOrModel));
            }

            if ($this->hasJsonpCallback()) {
                $return = $this->jsonpCallback . '(' . $return . ');';
            }
            return $return;
        }

        // use case 3: Both $nameOrModel and $values are populated
        throw new Exception\DomainException(sprintf(
            '%s: Do not know how to handle operation when both $nameOrModel and $values are populated',
            __METHOD__
        ));
    }

    /**
     * Can this renderer render trees of view models?
     *
     * Yes.
     *
     * @return true
     */
    public function canRenderTrees()
    {
        return true;
    }

    /**
     * Retrieve values from a model and recurse its children to build a data structure
     *
     * @param  Model $model
     * @param  bool $mergeWithVariables Whether or not to merge children with
     *         the variables of the $model
     * @return array
     */
    protected function recurseModel(Model $model, $mergeWithVariables = true)
    {
        $values = array();
        if ($mergeWithVariables) {
            $values = $model->getVariables();
        }

        if ($values instanceof Traversable) {
            $values = ArrayUtils::iteratorToArray($values);
        }

        if (!$model->hasChildren()) {
            return $values;
        }

        $mergeChildren = $this->mergeUnnamedChildren();
        foreach ($model as $child) {
            $captureTo = $child->captureTo();
            if (!$captureTo && !$mergeChildren) {
                // We don't want to do anything with this child
                continue;
            }

            $childValues = $this->recurseModel($child);
            if ($captureTo) {
                // Capturing to a specific key
                // TODO please complete if append is true. must change old
                // value to array and append to array?
                $values[$captureTo] = $childValues;
            } elseif ($mergeChildren) {
                // Merging values with parent
                $values = array_replace_recursive($values, $childValues);
            }
        }
        return $values;
    }

    /**
     * Inject discovered child model values into parent model
     *
     * @todo   detect collisions and decide whether to append and/or aggregate?
     * @param  Model $model
     * @param  array $children
     */
    protected function injectChildren(Model $model, array $children)
    {
        foreach ($children as $child => $value) {
            // TODO detect collisions and decide whether to append and/or aggregate?
            $model->setVariable($child, $value);
        }
    }
}
