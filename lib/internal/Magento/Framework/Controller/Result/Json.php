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
     * @param InlineInterface $translateInline
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        InlineInterface $translateInline,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->translateInline = $translateInline;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Set json data
     *
     * @param array|string|\Magento\Framework\DataObject $data
     * @param bool $cycleCheck
     * @param array $options
     * @return Json
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated
     */
    public function setData($data, $cycleCheck = false, $options = [])
    {
        if ($data instanceof \Magento\Framework\DataObject) {
            return $this->setArrayData($data->toArray());
        }

        if (is_array($data)) {
            return $this->setArrayData($data);
        }

        //Should we validate the json here?
        if (is_string($data)) {
            return $this->setJsonData($data);
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase('Invalid argument type')
        );
    }

    /**
     * Should cover this with a test or 2
     *
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setArrayData(array $data)
    {
        $this->setJsonData($this->serializer->serialize($data));
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
