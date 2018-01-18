<?php
namespace Magento\Webapi\Controller\Rest;


class RequestProcessorPool
{
    const REQUEST_PROCESSORS_ARRAY_OBJECT_KEY = 'object';

    /** @var \Magento\Webapi\Controller\Rest\RequestProcessorInterface[]  */
    private $requestProcessors;


    /**
     * RequestProcessorPool constructor.
     * @param array $requestProcessors
     */
    public function __construct($requestProcessors = [])
    {
        $this->requestProcessors = $this->_getSortedRequestProcessors($requestProcessors);
    }

    /**
     * @param array $requestProcessors
     * @return array
     */
    protected function _getSortedRequestProcessors($requestProcessors) {
        if (count($requestProcessors)) {
            uasort($requestProcessors, function($proc1, $proc2){
                $proc1["sortOrder"] = isset($proc1["sortOrder"]) ? (int) $proc1["sortOrder"] : 0;
                $proc2["sortOrder"] = isset($proc2["sortOrder"]) ? (int) $proc2["sortOrder"] : 0;
                return ($proc1["sortOrder"] <  $proc2["sortOrder"]) ? -1 : 1;
            });
            return array_column($requestProcessors, self::REQUEST_PROCESSORS_ARRAY_OBJECT_KEY);
        }
        return [];
    }

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return \Magento\Webapi\Controller\Rest\RequestProcessorInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getRequestProcessor(\Magento\Framework\Webapi\Rest\Request $request)
    {
        foreach ($this->requestProcessors as $requestProcessor) {
            if ($requestProcessor->canProcess($request)) {
                return $requestProcessor;
            }
        }
        throw new \Magento\Framework\Exception\NotFoundException(__('Specified request does not match any route.'));
    }

}