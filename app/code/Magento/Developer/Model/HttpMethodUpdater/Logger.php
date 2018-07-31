<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Model\HttpMethodUpdater;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Interception\InterceptorInterface;

class Logger
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LogRepository
     */
    private $repo;

    /**
     * @param RequestInterface $request
     * @param LogRepository $repository
     */
    public function __construct(
        RequestInterface $request,
        LogRepository $repository
    ) {
        $this->request = $request;
        $this->repo = $repository;
    }

    public function beforeExecute(ActionInterface $action)
    {
        if ($this->request instanceof HttpRequest) {
            if ($action instanceof InterceptorInterface) {
                $className = get_parent_class($action);
            } else {
                $className = get_class($action);
            }
            $method = $this->request->getMethod();
            $this->repo->log(new Log($className, $method));
        }

        return null;
    }
}
