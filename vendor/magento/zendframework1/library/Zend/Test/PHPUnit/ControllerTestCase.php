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
 * @package    Zend_Test
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Controller_Front */
#require_once 'Zend/Controller/Front.php';

/** @see Zend_Controller_Action_HelperBroker */
#require_once 'Zend/Controller/Action/HelperBroker.php';

/** @see Zend_Layout */
#require_once 'Zend/Layout.php';

/** @see Zend_Session */
#require_once 'Zend/Session.php';

/** @see Zend_Registry */
#require_once 'Zend/Registry.php';

/**
 * Functional testing scaffold for MVC applications
 *
 * @uses       PHPUnit_Framework_TestCase
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Test_PHPUnit_ControllerTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var mixed Bootstrap file path or callback
     */
    public $bootstrap;

    /**
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     * @var Zend_Dom_Query
     */
    protected $_query;

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * XPath namespaces
     * @var array
     */
    protected $_xpathNamespaces = array();

    /**
     * Overloading: prevent overloading to special properties
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Zend_Exception
     */
    public function __set($name, $value)
    {
        if (in_array($name, array('request', 'response', 'frontController'))) {
            #require_once 'Zend/Exception.php';
            throw new Zend_Exception(sprintf('Setting %s object manually is not allowed', $name));
        }
        $this->$name = $value;
    }

    /**
     * Overloading for common properties
     *
     * Provides overloading for request, response, and frontController objects.
     *
     * @param  mixed $name
     * @return null|Zend_Controller_Front|Zend_Controller_Request_HttpTestCase|Zend_Controller_Response_HttpTestCase
     */
    public function __get($name)
    {
        switch ($name) {
            case 'request':
                return $this->getRequest();
            case 'response':
                return $this->getResponse();
            case 'frontController':
                return $this->getFrontController();
        }

        return null;
    }

    /**
     * Set up MVC app
     *
     * Calls {@link bootstrap()} by default
     */
    protected function setUp()
    {
        $this->bootstrap();
    }

    /**
     * Bootstrap the front controller
     *
     * Resets the front controller, and then bootstraps it.
     *
     * If {@link $bootstrap} is a callback, executes it; if it is a file, it include's
     * it. When done, sets the test case request and response objects into the
     * front controller.
     */
    final public function bootstrap()
    {
        $this->reset();
        if (null !== $this->bootstrap) {
            if ($this->bootstrap instanceof Zend_Application) {
                $this->bootstrap->bootstrap();
                $this->_frontController = $this->bootstrap->getBootstrap()->getResource('frontcontroller');
            } elseif (is_callable($this->bootstrap)) {
                call_user_func($this->bootstrap);
            } elseif (is_string($this->bootstrap)) {
                #require_once 'Zend/Loader.php';
                if (Zend_Loader::isReadable($this->bootstrap)) {
                    include $this->bootstrap;
                }
            }
        }
        $this->frontController
             ->setRequest($this->getRequest())
             ->setResponse($this->getResponse());
    }

    /**
     * Dispatch the MVC
     *
     * If a URL is provided, sets it as the request URI in the request object.
     * Then sets test case request and response objects in front controller,
     * disables throwing exceptions, and disables returning the response.
     * Finally, dispatches the front controller.
     *
     * @param string|null $url
     */
    public function dispatch($url = null)
    {
        // redirector should not exit
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);

        // json helper should not exit
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;

        $request    = $this->getRequest();
        if (null !== $url) {
            $request->setRequestUri($url);
        }
        $request->setPathInfo(null);

        $controller = $this->getFrontController();
        $this->frontController
             ->setRequest($request)
             ->setResponse($this->getResponse())
             ->throwExceptions(false)
             ->returnResponse(false);

        if ($this->bootstrap instanceof Zend_Application) {
            $this->bootstrap->run();
        } else {
            $this->frontController->dispatch();
        }
    }

    /**
     * Reset MVC state
     *
     * Creates new request/response objects, resets the front controller
     * instance, and resets the action helper broker.
     *
     * @todo   Need to update Zend_Layout to add a resetInstance() method
     */
    public function reset()
    {
        $_SESSION = array();
        $_GET     = array();
        $_POST    = array();
        $_COOKIE  = array();
        $this->resetRequest();
        $this->resetResponse();
        Zend_Layout::resetMvcInstance();
        Zend_Controller_Action_HelperBroker::resetHelpers();
        $this->frontController->resetInstance();
        Zend_Session::$_unitTestEnabled = true;
    }

    /**
     * Rest all view placeholders
     */
    protected function _resetPlaceholders()
    {
        $registry = Zend_Registry::getInstance();
        $remove   = array();
        foreach ($registry as $key => $value) {
            if (strstr($key, '_View_')) {
                $remove[] = $key;
            }
        }

        foreach ($remove as $key) {
            unset($registry[$key]);
        }
    }

    /**
     * Reset the request object
     *
     * Useful for test cases that need to test multiple trips to the server.
     *
     * @return Zend_Test_PHPUnit_ControllerTestCase
     */
    public function resetRequest()
    {
        if ($this->_request instanceof Zend_Controller_Request_HttpTestCase) {
            $this->_request->clearQuery()
                           ->clearPost();
        }
        $this->_request = null;
        return $this;
    }

    /**
     * Reset the response object
     *
     * Useful for test cases that need to test multiple trips to the server.
     *
     * @return Zend_Test_PHPUnit_ControllerTestCase
     */
    public function resetResponse()
    {
        $this->_response = null;
        $this->_resetPlaceholders();
        return $this;
    }

    /**
     * Assert against DOM selection
     *
     * @param string $path CSS selector path
     * @param string $message
     */
    public function assertQuery($path, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection
     *
     * @param string $path CSS selector path
     * @param string $message
     */
    public function assertNotQuery($path, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; node should contain content
     *
     * @param string $path CSS selector path
     * @param string $match content that should be contained in matched nodes
     * @param string $message
     */
    public function assertQueryContentContains($path, $match, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $match)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; node should NOT contain content
     *
     * @param string $path CSS selector path
     * @param string $match content that should NOT be contained in matched nodes
     * @param string $message
     */
    public function assertNotQueryContentContains($path, $match, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $match)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; node should match content
     *
     * @param string $path CSS selector path
     * @param string $pattern Pattern that should be contained in matched nodes
     * @param string $message
     */
    public function assertQueryContentRegex($path, $pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $pattern)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; node should NOT match content
     *
     * @param string $path CSS selector path
     * @param string $pattern pattern that should NOT be contained in matched nodes
     * @param string $message
     */
    public function assertNotQueryContentRegex($path, $pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $pattern)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; should contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param string $count Number of nodes that should match
     * @param string $message
     */
    public function assertQueryCount($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; should NOT contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param string $count Number of nodes that should NOT match
     * @param string $message
     */
    public function assertNotQueryCount($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; should contain at least this number of nodes
     *
     * @param string $path CSS selector path
     * @param string $count Minimum number of nodes that should match
     * @param string $message
     */
    public function assertQueryCountMin($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against DOM selection; should contain no more than this number of nodes
     *
     * @param string $path CSS selector path
     * @param string $count Maximum number of nodes that should match
     * @param string $message
     */
    public function assertQueryCountMax($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Register XPath namespaces
     *
     * @param array $xpathNamespaces
     */
    public function registerXpathNamespaces($xpathNamespaces)
    {
        $this->_xpathNamespaces = $xpathNamespaces;
    }

    /**
     * Assert against XPath selection
     *
     * @param string $path XPath path
     * @param string $message
     */
    public function assertXpath($path, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection
     *
     * @param string $path XPath path
     * @param string $message
     */
    public function assertNotXpath($path, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; node should contain content
     *
     * @param string $path XPath path
     * @param string $match content that should be contained in matched nodes
     * @param string $message
     */
    public function assertXpathContentContains($path, $match, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $match)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; node should NOT contain content
     *
     * @param string $path XPath path
     * @param string $match content that should NOT be contained in matched nodes
     * @param string $message
     */
    public function assertNotXpathContentContains($path, $match, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $match)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; node should match content
     *
     * @param string $path XPath path
     * @param string $pattern Pattern that should be contained in matched nodes
     * @param string $message
     */
    public function assertXpathContentRegex($path, $pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $pattern)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; node should NOT match content
     *
     * @param string $path XPath path
     * @param string $pattern pattern that should NOT be contained in matched nodes
     * @param string $message
     */
    public function assertNotXpathContentRegex($path, $pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $pattern)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; should contain exact number of nodes
     *
     * @param string $path XPath path
     * @param string $count Number of nodes that should match
     * @param string $message
     */
    public function assertXpathCount($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; should NOT contain exact number of nodes
     *
     * @param string $path XPath path
     * @param string $count Number of nodes that should NOT match
     * @param string $message
     */
    public function assertNotXpathCount($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; should contain at least this number of nodes
     *
     * @param string $path XPath path
     * @param string $count Minimum number of nodes that should match
     * @param string $message
     */
    public function assertXpathCountMin($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert against XPath selection; should contain no more than this number of nodes
     *
     * @param string $path XPath path
     * @param string $count Maximum number of nodes that should match
     * @param string $message
     */
    public function assertXpathCountMax($path, $count, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/DomQuery.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_DomQuery($path);
        $constraint->registerXpathNamespaces($this->_xpathNamespaces);
        $content    = $this->response->outputBody();
        if (!$constraint->evaluate($content, __FUNCTION__, $count)) {
            $constraint->fail($path, $message);
        }
    }

    /**
     * Assert that response is a redirect
     *
     * @param string $message
     */
    public function assertRedirect($message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/Redirect.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_Redirect();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert that response is NOT a redirect
     *
     * @param string $message
     */
    public function assertNotRedirect($message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/Redirect.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_Redirect();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert that response redirects to given URL
     *
     * @param string $url
     * @param string $message
     */
    public function assertRedirectTo($url, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/Redirect.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_Redirect();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $url)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert that response does not redirect to given URL
     *
     * @param string $url
     * @param string $message
     */
    public function assertNotRedirectTo($url, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/Redirect.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_Redirect();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $url)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert that redirect location matches pattern
     *
     * @param string $pattern
     * @param string $message
     */
    public function assertRedirectRegex($pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/Redirect.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_Redirect();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $pattern)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert that redirect location does not match pattern
     *
     * @param string $pattern
     * @param string $message
     */
    public function assertNotRedirectRegex($pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/Redirect.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_Redirect();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $pattern)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response code
     *
     * @param int $code
     * @param string $message
     */
    public function assertResponseCode($code, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $code)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response code
     *
     * @param int $code
     * @param string $message
     */
    public function assertNotResponseCode($code, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $constraint->setNegate(true);
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $code)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response header exists
     *
     * @param string $header
     * @param string $message
     */
    public function assertHeader($header, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $header)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response header does not exist
     *
     * @param string $header
     * @param string $message
     */
    public function assertNotHeader($header, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $constraint->setNegate(true);
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $header)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response header exists and contains the given string
     *
     * @param string $header
     * @param string $match
     * @param string $message
     */
    public function assertHeaderContains($header, $match, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $header, $match)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response header does not exist and/or does not contain the given string
     *
     * @param string $header
     * @param string $match
     * @param string $message
     */
    public function assertNotHeaderContains($header, $match, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $constraint->setNegate(true);
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $header, $match)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response header exists and matches the given pattern
     *
     * @param string $header
     * @param string $pattern
     * @param string $message
     */
    public function assertHeaderRegex($header, $pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $header, $pattern)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert response header does not exist and/or does not match the given regex
     *
     * @param string $header
     * @param string $pattern
     * @param string $message
     */
    public function assertNotHeaderRegex($header, $pattern, $message = '')
    {
        $this->_incrementAssertionCount();
        #require_once 'Zend/Test/PHPUnit/Constraint/ResponseHeader.php';
        $constraint = new Zend_Test_PHPUnit_Constraint_ResponseHeader();
        $constraint->setNegate(true);
        $response   = $this->response;
        if (!$constraint->evaluate($response, __FUNCTION__, $header, $pattern)) {
            $constraint->fail($response, $message);
        }
    }

    /**
     * Assert that the last handled request used the given module
     *
     * @param string $module
     * @param string $message
     */
    public function assertModule($module, $message = '')
    {
        $this->_incrementAssertionCount();
        if ($module != $this->request->getModuleName()) {
            $msg = sprintf('Failed asserting last module used <"%s"> was "%s"',
                $this->request->getModuleName(),
                $module
            );
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the last handled request did NOT use the given module
     *
     * @param string $module
     * @param string $message
     */
    public function assertNotModule($module, $message = '')
    {
        $this->_incrementAssertionCount();
        if ($module == $this->request->getModuleName()) {
            $msg = sprintf('Failed asserting last module used was NOT "%s"', $module);
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the last handled request used the given controller
     *
     * @param string $controller
     * @param string $message
     */
    public function assertController($controller, $message = '')
    {
        $this->_incrementAssertionCount();
        if ($controller != $this->request->getControllerName()) {
            $msg = sprintf('Failed asserting last controller used <"%s"> was "%s"',
                $this->request->getControllerName(),
                $controller
            );
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the last handled request did NOT use the given controller
     *
     * @param  string $controller
     * @param  string $message
     */
    public function assertNotController($controller, $message = '')
    {
        $this->_incrementAssertionCount();
        if ($controller == $this->request->getControllerName()) {
            $msg = sprintf('Failed asserting last controller used <"%s"> was NOT "%s"',
                $this->request->getControllerName(),
                $controller
            );
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the last handled request used the given action
     *
     * @param string $action
     * @param string $message
     */
    public function assertAction($action, $message = '')
    {
        $this->_incrementAssertionCount();
        if ($action != $this->request->getActionName()) {
            $msg = sprintf('Failed asserting last action used <"%s"> was "%s"', $this->request->getActionName(), $action);
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the last handled request did NOT use the given action
     *
     * @param string $action
     * @param string $message
     */
    public function assertNotAction($action, $message = '')
    {
        $this->_incrementAssertionCount();
        if ($action == $this->request->getActionName()) {
            $msg = sprintf('Failed asserting last action used <"%s"> was NOT "%s"', $this->request->getActionName(), $action);
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the specified route was used
     *
     * @param string $route
     * @param string $message
     */
    public function assertRoute($route, $message = '')
    {
        $this->_incrementAssertionCount();
        $router = $this->frontController->getRouter();
        if ($route != $router->getCurrentRouteName()) {
            $msg = sprintf('Failed asserting matched route was "%s", actual route is %s',
                $route,
                $router->getCurrentRouteName()
            );
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Assert that the route matched is NOT as specified
     *
     * @param string $route
     * @param string $message
     */
    public function assertNotRoute($route, $message = '')
    {
        $this->_incrementAssertionCount();
        $router = $this->frontController->getRouter();
        if ($route == $router->getCurrentRouteName()) {
            $msg = sprintf('Failed asserting route matched was NOT "%s"', $route);
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }

    /**
     * Retrieve front controller instance
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_frontController) {
            $this->_frontController = Zend_Controller_Front::getInstance();
        }
        return $this->_frontController;
    }

    /**
     * Retrieve test case request object
     *
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            #require_once 'Zend/Controller/Request/HttpTestCase.php';
            $this->_request = new Zend_Controller_Request_HttpTestCase;
        }
        return $this->_request;
    }

    /**
     * Retrieve test case response object
     *
     * @return Zend_Controller_Response_HttpTestCase
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            #require_once 'Zend/Controller/Response/HttpTestCase.php';
            $this->_response = new Zend_Controller_Response_HttpTestCase;
        }
        return $this->_response;
    }

    /**
     * Retrieve DOM query object
     *
     * @return Zend_Dom_Query
     */
    public function getQuery()
    {
        if (null === $this->_query) {
            #require_once 'Zend/Dom/Query.php';
            $this->_query = new Zend_Dom_Query;
        }
        return $this->_query;
    }

    /**
     * URL Helper
     *
     * @param  array  $urlOptions
     * @param  string $name
     * @param  bool   $reset
     * @param  bool   $encode
     * @throws Exception
     * @throws Zend_Controller_Router_Exception
     * @return string
     */
    public function url($urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $frontController = $this->getFrontController();
        $router = $frontController->getRouter();
        if (!$router instanceof Zend_Controller_Router_Rewrite) {
            throw new Exception('This url helper utility function only works when the router is of type Zend_Controller_Router_Rewrite');
        }
        if (count($router->getRoutes()) == 0) {
            $router->addDefaultRoutes();
        }
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }

    /**
     * Urlize options
     *
     * @param  array $urlOptions
     * @param  bool  $actionControllerModuleOnly
     * @return mixed
     */
    public function urlizeOptions($urlOptions, $actionControllerModuleOnly = true)
    {
        $ccToDash = new Zend_Filter_Word_CamelCaseToDash();
        foreach ($urlOptions as $n => $v) {
            if (in_array($n, array('action', 'controller', 'module'))) {
                $urlOptions[$n] = $ccToDash->filter($v);
            }
        }
        return $urlOptions;
    }

    /**
     * Increment assertion count
     */
    protected function _incrementAssertionCount()
    {
        $stack = debug_backtrace();
        foreach ($stack as $step) {
            if (isset($step['object'])
                && $step['object'] instanceof PHPUnit_Framework_TestCase
            ) {
                if (version_compare(PHPUnit_Runner_Version::id(), '3.3.0', 'lt')) {
                    break;
                } elseif (version_compare(PHPUnit_Runner_Version::id(), '3.3.3', 'lt')) {
                    $step['object']->incrementAssertionCounter();
                } else {
                    $step['object']->addToAssertionCount(1);
                }
                break;
            }
        }
    }
}
