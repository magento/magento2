<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\Translate\InlineInterface;

/**
 * A possible implementation of JSON response type (instead of hardcoding json_encode() all over the place)
 * Actual for controller actions that serve ajax requests
 *
 * @api
 * @since 100.0.2
 */
class Json extends AbstractResult
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var string
     */
    protected $json;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        InlineInterface $translateInline,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->translateInline = $translateInline;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Set json data
     *
     * @param mixed $data
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setData($data, $cycleCheck = false, $options = [])
    {
        if ($data instanceof \Magento\Framework\DataObject) {
            $data = $data->toArray();
        }
        $this->json = $this->serializer->serialize($data);
        return $this;
    }

    /**
     * @param string $jsonData
     * @return $this
     */
    public function setJsonData($jsonData)
    {
        $this->json = (string)$jsonData;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(HttpResponseInterface $response)
    {
        $this->translateInline->processResponseBody($this->json, true);
        $response->setHeader('Content-Type', 'application/json', true);
        $response->setBody($this->json);
        return $this;
    }
}
