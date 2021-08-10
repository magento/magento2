<?php
/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-server/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Api\Data;

/**
 * Server Interface
 */
interface ServerInterface
{
    /**
     * Attach a function as a server method
     *
     * Namespacing is primarily for xmlrpc, but may be used with other
     * implementations to prevent naming collisions.
     *
     * @param  string|array $function
     * @param  string $namespace
     * @param  null|array Optional array of arguments to pass to callback at
     *                    dispatch.
     * @return void
     */
    public function addFunction($function, $namespace = '');

    /**
     * Attach a class to a server
     *
     * The individual implementations should probably allow passing a variable
     * number of arguments in, so that developers may define custom runtime
     * arguments to pass to server methods.
     *
     * Namespacing is primarily for xmlrpc, but could be used for other
     * implementations as well.
     *
     * @param  mixed $class Class name or object instance to examine and attach
     *                      to the server.
     * @param  string $namespace Optional namespace with which to prepend method
     *                           names in the dispatch table.
     *                           methods in the class will be valid callbacks.
     * @param  null|array Optional array of arguments to pass to callbacks at
     *                    dispatch.
     * @return void
     */
    public function setClass($class, $namespace = '', $argv = null);

    /**
     * Generate a server fault
     *
     * @param  mixed $fault
     * @param  int $code
     * @return mixed
     */
    public function fault($fault = null, $code = 404);

    /**
     * Handle a request
     *
     * Requests may be passed in, or the server may automatically determine the
     * request based on defaults. Dispatches server request to appropriate
     * method and returns a response
     *
     * @param  mixed $request
     * @return mixed
     */
    public function handle($request = false);

    /**
     * Return a server definition array
     *
     * Returns a server definition array as created using
     * {@link Reflection}. Can be used for server introspection,
     * documentation, or persistence.
     *
     * @return array
     */
    public function getFunctions();

    /**
     * Load server definition
     *
     * Used for persistence; loads a construct as returned by {@link getFunctions()}.
     *
     * @param  array $definition
     * @return void
     */
    public function loadFunctions($definition);

    /**
     * Set server persistence
     *
     * @todo Determine how to implement this
     * @param  int $mode
     * @return void
     */
    public function setPersistence($mode);

    /**
     * Sets auto-response flag for the server.
     *
     * To unify all servers, default behavior should be to auto-emit response.
     *
     * @param  bool $flag
     * @return ServerInterface Self instance.
     */
    public function setReturnResponse($flag = true);

    /**
     * Returns auto-response flag of the server.
     *
     * @return bool $flag Current status.
     */
    public function getReturnResponse();

    /**
     * Returns last produced response.
     *
     * @return string|object Content of last response, or response object that
     *                       implements __toString() methods.
     */
    public function getResponse();
}
