<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Plugin\Controller;

use Magento\Contact\Controller\Index\Index;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Class IndexPlugin
 *
 * @package Magento\Contact\Plugin\Controller
 */
class IndexPlugin
{
    /** @var RedirectInterface $redirect */
    private $redirect;

    /**
     * IndexPlugin constructor.
     *
     * @param RedirectInterface $redirect
     */
    public function __construct(RedirectInterface $redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * Contact Redirect Index Controller before Plugin
     *
     * @param Index $subject
     *
     * @return null
     */
    public function beforeExecute(Index $subject)
    {
        /** @var Http|RequestInterface $request */
        $request = $subject->getRequest();

        /** @var string $RequestString */
        $requestString = $request->getRequestString();

        if ((bool) preg_match('/.+[index]/', $requestString) === true) {
            $this->redirect->redirect($subject->getResponse(), 'contact');
        }
        return null;
    }
}
