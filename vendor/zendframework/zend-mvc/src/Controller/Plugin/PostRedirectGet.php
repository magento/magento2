<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use Zend\Mvc\Exception\RuntimeException;
use Zend\Session\Container;

/**
 * Plugin to help facilitate Post/Redirect/Get (http://en.wikipedia.org/wiki/Post/Redirect/Get)
 */
class PostRedirectGet extends AbstractPlugin
{
    /**
     * @var Container
     */
    protected $sessionContainer;

    /**
     * Perform PRG logic
     *
     * If a null value is present for the $redirect, the current route is
     * retrieved and use to generate the URL for redirect.
     *
     * If the request method is POST, creates a session container set to expire
     * after 1 hop containing the values of the POST. It then redirects to the
     * specified URL using a status 303.
     *
     * If the request method is GET, checks to see if we have values in the
     * session container, and, if so, returns them; otherwise, it returns a
     * boolean false.
     *
     * @param  null|string $redirect
     * @param  bool        $redirectToUrl
     * @return \Zend\Http\Response|array|\Traversable|false
     */
    public function __invoke($redirect = null, $redirectToUrl = false)
    {
        $controller = $this->getController();
        $request    = $controller->getRequest();
        $container  = $this->getSessionContainer();

        if ($request->isPost()) {
            $container->setExpirationHops(1, 'post');
            $container->post = $request->getPost()->toArray();
            return $this->redirect($redirect, $redirectToUrl);
        } else {
            if (null !== $container->post) {
                $post = $container->post;
                unset($container->post);
                return $post;
            }

            return false;
        }
    }

    /**
     * @return Container
     */
    public function getSessionContainer()
    {
        if (!isset($this->sessionContainer)) {
            $this->sessionContainer = new Container('prg_post1');
        }
        return $this->sessionContainer;
    }

    /**
     * @param  Container $container
     * @return PostRedirectGet
     */
    public function setSessionContainer(Container $container)
    {
        $this->sessionContainer = $container;
        return $this;
    }

    /**
     * TODO: Good candidate for traits method in PHP 5.4 with FilePostRedirectGet plugin
     *
     * @param  string  $redirect
     * @param  bool    $redirectToUrl
     * @return \Zend\Http\Response
     * @throws \Zend\Mvc\Exception\RuntimeException
     */
    protected function redirect($redirect, $redirectToUrl)
    {
        $controller         = $this->getController();
        $params             = array();
        $options            = array('query' => $controller->params()->fromQuery());
        $reuseMatchedParams = false;

        if (null === $redirect) {
            $routeMatch = $controller->getEvent()->getRouteMatch();

            $redirect = $routeMatch->getMatchedRouteName();
            //null indicates to redirect for self.
            $reuseMatchedParams = true;
        }

        if (method_exists($controller, 'getPluginManager')) {
            // get the redirect plugin from the plugin manager
            $redirector = $controller->getPluginManager()->get('Redirect');
        } else {
            /*
             * If the user wants to redirect to a route, the redirector has to come
             * from the plugin manager -- otherwise no router will be injected
             */
            if (false === $redirectToUrl) {
                throw new RuntimeException('Could not redirect to a route without a router');
            }

            $redirector = new Redirect();
        }

        if (false === $redirectToUrl) {
            $response = $redirector->toRoute($redirect, $params, $options, $reuseMatchedParams);
            $response->setStatusCode(303);
            return $response;
        }

        $response = $redirector->toUrl($redirect);
        $response->setStatusCode(303);

        return $response;
    }
}
