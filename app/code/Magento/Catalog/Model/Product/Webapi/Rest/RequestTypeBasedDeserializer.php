<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Webapi\Rest;

use Magento\Framework\Webapi\Rest\Request\DeserializerInterface;
use Magento\Framework\Webapi\Rest\Request\DeserializerFactory;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class RequestTypeBasedDeserializer
 *
 * Used for deserialization rest request body.
 * Runs appropriate deserialization class object based on request body content type.
 */
class RequestTypeBasedDeserializer implements DeserializerInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var DeserializerFactory
     */
    private $deserializeFactory;

    /**
     * RequestTypeBasedDeserializer constructor.
     *
     * @param DeserializerFactory $deserializeFactory
     * @param Request $request
     */
    public function __construct(
        DeserializerFactory $deserializeFactory,
        Request $request
    ) {
        $this->deserializeFactory = $deserializeFactory;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     *
     * Parse request body into array of params with identifying request body content type
     * to use appropriate instance of deserializer class
     *
     * @param string $body Posted content from request
     * @return array|null Return NULL if content is invalid
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function deserialize($body)
    {
        $deserializer = $this->deserializeFactory->get($this->request->getContentType());
        return $deserializer->deserialize($body);
    }
}
