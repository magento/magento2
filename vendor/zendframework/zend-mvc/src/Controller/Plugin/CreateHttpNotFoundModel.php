<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class CreateHttpNotFoundModel extends AbstractPlugin
{
    /**
     * Create an HTTP view model representing a "not found" page
     *
     * @param  Response $response
     *
     * @return ViewModel
     */
    public function __invoke(Response $response)
    {
        $response->setStatusCode(404);

        return new ViewModel(array('content' => 'Page not found'));
    }
}
