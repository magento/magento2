<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest\Response;

use Magento\Framework\Phrase;

/**
 * Factory of REST renders
 */
class RendererFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_renders;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param array $renders
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        array $renders = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_request = $request;
        $this->_renders = $renders;
    }

    /**
     * Get renderer for Mime-Type specified in Accept header of request.
     *
     * @return \Magento\Framework\Webapi\Rest\Response\RendererInterface
     * @throws \Magento\Framework\Webapi\Exception
     * @throws \LogicException
     */
    public function get()
    {
        $renderer = $this->_objectManager->get($this->_getRendererClass());
        if (!$renderer instanceof \Magento\Framework\Webapi\Rest\Response\RendererInterface) {
            throw new \LogicException(
                'The renderer must implement "Magento\Framework\Webapi\Rest\Response\RendererInterface".'
            );
        }
        return $renderer;
    }

    /**
     * Find renderer which can render response in requested format.
     *
     * @return string
     * @throws \Magento\Framework\Webapi\Exception
     */
    protected function _getRendererClass()
    {
        $acceptTypes = $this->getAcceptTypes();
        foreach ($acceptTypes as $acceptType) {
            foreach ($this->_renders as $rendererConfig) {
                $rendererType = $rendererConfig['type'];
                if ($acceptType == $rendererType
                    || $acceptType == current(explode('/', $rendererType ?? '')) . '/*'
                    || $acceptType == '*/*'
                ) {
                    return $rendererConfig['model'];
                }
            }
        }
        /** If server does not have renderer for any of the accepted types it SHOULD send 406 (not acceptable). */
        throw new \Magento\Framework\Webapi\Exception(
            new Phrase(
                'Server cannot match any of the given Accept HTTP header media type(s) from the request: "%1" '.
                'with media types from the config of response renderer.',
                $acceptTypes
            ),
            0,
            \Magento\Framework\Webapi\Exception::HTTP_NOT_ACCEPTABLE
        );
    }

    /**
     * Retrieve accept types understandable by requester in a form of array sorted by quality in descending order.
     *
     * @return string[]
     */
    private function getAcceptTypes()
    {
        $qualityToTypes = [];
        $orderedTypes = [];

        foreach (preg_split('/,\s*/', $this->_request->getHeader('Accept') ?? '') as $definition) {
            $typeWithQ = explode(';', $definition);
            $mimeType = trim(array_shift($typeWithQ));

            // check MIME type validity
            if (!preg_match('~^([0-9a-z*+\-]+)(?:/([0-9a-z*+\-\.]+))?$~i', $mimeType)) {
                continue;
            }
            $quality = '1.0';
            // default value for quality

            if ($typeWithQ) {
                $qAndValue = explode('=', $typeWithQ[0]);

                if (2 == count($qAndValue)) {
                    $quality = $qAndValue[1];
                }
            }
            $qualityToTypes[$quality][$mimeType] = true;
        }
        krsort($qualityToTypes);

        foreach ($qualityToTypes as $typeList) {
            $orderedTypes += $typeList;
        }
        return empty($orderedTypes) ? ['*/*'] : array_keys($orderedTypes);
    }
}
