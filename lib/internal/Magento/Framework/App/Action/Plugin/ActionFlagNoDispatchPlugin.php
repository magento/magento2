<?php declare(strict_types=1);

namespace Magento\Framework\App\Action\Plugin;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;

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

    public function __construct(ActionFlag $actionFlag, ResponseInterface $response)
    {
        $this->actionFlag = $actionFlag;
        $this->response = $response;
    }

    /**
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
