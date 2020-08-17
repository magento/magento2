<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Action\Plugin;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Do not call Action::execute() if the action flag FLAG_NO_DISPATCH is set.
 */
class ActionFlagNoDispatchPlugin
{
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ActionFlag $actionFlag
     * @param ResponseInterface $response
     */
    public function __construct(ActionFlag $actionFlag, ResponseInterface $response)
    {
        $this->actionFlag = $actionFlag;
        $this->response = $response;
    }

    /**
     * Do not call proceed if the no dispatch action flag is set.
     *
     * @param ActionInterface $subject
     * @param callable $proceed
     * @return ResponseInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(ActionInterface $subject, callable $proceed)
    {
        return $this->actionFlag->get('', ActionInterface::FLAG_NO_DISPATCH) ? $this->response : $proceed();
    }
}
