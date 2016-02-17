<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\Http\Response;
use Zend\Json\Json as JsonFormatter;

/**
 * Helper for simplifying JSON responses
 */
class Json extends AbstractHelper
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * Encode data as JSON and set response header
     *
     * @param  mixed $data
     * @param  array $jsonOptions Options to pass to JsonFormatter::encode()
     * @return string|void
     */
    public function __invoke($data, array $jsonOptions = array())
    {
        $data = JsonFormatter::encode($data, null, $jsonOptions);

        if ($this->response instanceof Response) {
            $headers = $this->response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');
        }

        return $data;
    }

    /**
     * Set the response object
     *
     * @param  Response $response
     * @return Json
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }
}
