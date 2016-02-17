<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use Zend\Filter\FilterChain;
use Zend\Form\FormInterface;
use Zend\Http\Response;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Exception\RuntimeException;
use Zend\Session\Container;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\ValidatorChain;

/**
 * Plugin to help facilitate Post/Redirect/Get for file upload forms
 * (http://en.wikipedia.org/wiki/Post/Redirect/Get)
 *
 * Requires that the Form's File inputs contain a 'fileRenameUpload' filter
 * with the target option set: 'target' => /valid/target/path'.
 * This is so the files are moved to a new location between requests.
 * If this filter is not added, the temporary upload files will disappear
 * between requests.
 */
class FilePostRedirectGet extends AbstractPlugin
{
    /**
     * @var Container
     */
    protected $sessionContainer;

    /**
     * @param  FormInterface $form
     * @param  string        $redirect      Route or URL string (default: current route)
     * @param  bool          $redirectToUrl Use $redirect as a URL string (default: false)
     * @return bool|array|Response
     */
    public function __invoke(FormInterface $form, $redirect = null, $redirectToUrl = false)
    {
        $request = $this->getController()->getRequest();
        if ($request->isPost()) {
            return $this->handlePostRequest($form, $redirect, $redirectToUrl);
        } else {
            return $this->handleGetRequest($form);
        }
    }

    /**
     * @param  FormInterface $form
     * @param  string        $redirect      Route or URL string (default: current route)
     * @param  bool          $redirectToUrl Use $redirect as a URL string (default: false)
     * @return Response
     */
    protected function handlePostRequest(FormInterface $form, $redirect, $redirectToUrl)
    {
        $container = $this->getSessionContainer();
        $request   = $this->getController()->getRequest();
        $postFiles = $request->getFiles()->toArray();
        $postOther = $request->getPost()->toArray();
        $post      = ArrayUtils::merge($postOther, $postFiles, true);

        // Fill form with the data first, collections may alter the form/filter structure
        $form->setData($post);

        // Change required flag to false for any previously uploaded files
        $inputFilter   = $form->getInputFilter();
        $previousFiles = ($container->files) ?: array();
        $this->traverseInputs(
            $inputFilter,
            $previousFiles,
            function ($input, $value) {
                if ($input instanceof FileInput) {
                    $input->setRequired(false);
                }
                return $value;
            }
        );

        // Run the form validations/filters and retrieve any errors
        $isValid = $form->isValid();
        $data    = $form->getData(FormInterface::VALUES_AS_ARRAY);
        $errors  = (!$isValid) ? $form->getMessages() : null;

        // Merge and replace previous files with new valid files
        $prevFileData = $this->getEmptyUploadData($inputFilter, $previousFiles);
        $newFileData  = $this->getNonEmptyUploadData($inputFilter, $data);
        $postFiles = ArrayUtils::merge(
            $prevFileData ?: array(),
            $newFileData  ?: array(),
            true
        );
        $post = ArrayUtils::merge($postOther, $postFiles, true);

        // Save form data in session
        $container->setExpirationHops(1, array('post', 'errors', 'isValid'));
        $container->post    = $post;
        $container->errors  = $errors;
        $container->isValid = $isValid;
        $container->files   = $postFiles;

        return $this->redirect($redirect, $redirectToUrl);
    }

    /**
     * @param  FormInterface $form
     * @return bool|array
     */
    protected function handleGetRequest(FormInterface $form)
    {
        $container = $this->getSessionContainer();
        if (null === $container->post) {
            // No previous post, bail early
            unset($container->files);
            return false;
        }

        // Collect data from session
        $post          = $container->post;
        $errors        = $container->errors;
        $isValid       = $container->isValid;
        unset($container->post);
        unset($container->errors);
        unset($container->isValid);

        // Fill form with the data first, collections may alter the form/filter structure
        $form->setData($post);

        // Remove File Input validators and filters on previously uploaded files
        // in case $form->isValid() or $form->bindValues() is run
        $inputFilter = $form->getInputFilter();
        $this->traverseInputs(
            $inputFilter,
            $post,
            function ($input, $value) {
                if ($input instanceof FileInput) {
                    $input->setAutoPrependUploadValidator(false)
                          ->setValidatorChain(new ValidatorChain())
                          ->setFilterChain(new FilterChain);
                }
                return $value;
            }
        );

        // set previous state
        $form->isValid(); // re-validate to bind values
        if (null !== $errors) {
            $form->setMessages($errors); // overwrite messages
        }
        $this->setProtectedFormProperty($form, 'isValid', $isValid); // force previous state

        // Clear previous files from session data if form was valid
        if ($isValid) {
            unset($container->files);
        }

        return $post;
    }

    /**
     * @return Container
     */
    public function getSessionContainer()
    {
        if (!isset($this->sessionContainer)) {
            $this->sessionContainer = new Container('file_prg_post1');
        }
        return $this->sessionContainer;
    }

    /**
     * @param  Container $container
     * @return FilePostRedirectGet
     */
    public function setSessionContainer(Container $container)
    {
        $this->sessionContainer = $container;
        return $this;
    }

    /**
     * @param  FormInterface $form
     * @param  string $property
     * @param  mixed  $value
     * @return FilePostRedirectGet
     */
    protected function setProtectedFormProperty(FormInterface $form, $property, $value)
    {
        $formClass = new \ReflectionClass($form);
        $property  = $formClass->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($form, $value);
        return $this;
    }

    /**
     * Traverse the InputFilter and run a callback against each Input and associated value
     *
     * @param  InputFilterInterface $inputFilter
     * @param  array                $values
     * @param  callable             $callback
     * @return array|null
     */
    protected function traverseInputs(InputFilterInterface $inputFilter, $values, $callback)
    {
        $returnValues = null;
        foreach ($values as $name => $value) {
            if (!$inputFilter->has($name)) {
                continue;
            }

            $input = $inputFilter->get($name);
            if ($input instanceof InputFilterInterface && is_array($value)) {
                $retVal = $this->traverseInputs($input, $value, $callback);
                if (null !== $retVal) {
                    $returnValues[$name] = $retVal;
                }
                continue;
            }

            $retVal = $callback($input, $value);
            if (null !== $retVal) {
                $returnValues[$name] = $retVal;
            }
        }
        return $returnValues;
    }

    /**
     * Traverse the InputFilter and only return the data of FileInputs that have an upload
     *
     * @param  InputFilterInterface $inputFilter
     * @param  array                $data
     * @return array
     */
    protected function getNonEmptyUploadData(InputFilterInterface $inputFilter, $data)
    {
        return $this->traverseInputs(
            $inputFilter,
            $data,
            function ($input, $value) {
                $messages = $input->getMessages();
                if (is_array($value) && $input instanceof FileInput && empty($messages)) {
                    $rawValue = $input->getRawValue();
                    if (
                        (isset($rawValue['error']) && $rawValue['error'] !== UPLOAD_ERR_NO_FILE)
                        || (isset($rawValue[0]['error']) && $rawValue[0]['error'] !== UPLOAD_ERR_NO_FILE)
                    ) {
                        return $value;
                    }
                }
                return;
            }
        );
    }

    /**
     * Traverse the InputFilter and only return the data of FileInputs that are empty
     *
     * @param  InputFilterInterface $inputFilter
     * @param  array                $data
     * @return array
     */
    protected function getEmptyUploadData(InputFilterInterface $inputFilter, $data)
    {
        return $this->traverseInputs(
            $inputFilter,
            $data,
            function ($input, $value) {
                $messages = $input->getMessages();
                if (is_array($value) && $input instanceof FileInput && empty($messages)) {
                    $rawValue = $input->getRawValue();
                    if ((isset($rawValue['error'])    && $rawValue['error']    === UPLOAD_ERR_NO_FILE)
                        || (isset($rawValue[0]['error']) && $rawValue[0]['error'] === UPLOAD_ERR_NO_FILE)
                    ) {
                        return $value;
                    }
                }
                return;
            }
        );
    }

    /**
     * TODO: Good candidate for traits method in PHP 5.4 with PostRedirectGet plugin
     *
     * @param  string  $redirect
     * @param  bool    $redirectToUrl
     * @return Response
     * @throws \Zend\Mvc\Exception\RuntimeException
     */
    protected function redirect($redirect, $redirectToUrl)
    {
        $controller         = $this->getController();
        $params             = array();
        $options            = array();
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
            if ($redirectToUrl === false) {
                throw new RuntimeException('Could not redirect to a route without a router');
            }

            $redirector = new Redirect();
        }

        if ($redirectToUrl === false) {
            $response = $redirector->toRoute($redirect, $params, $options, $reuseMatchedParams);
            $response->setStatusCode(303);
            return $response;
        }

        $response = $redirector->toUrl($redirect);
        $response->setStatusCode(303);

        return $response;
    }
}
