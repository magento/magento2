<?php
/**
 * FormUrlencoded deserializer of REST request content.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest\Request\Deserializer;

use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use InvalidArgumentException;
use Magento\Framework\Webapi\Rest\Request\DeserializerInterface;
use Zend\Stdlib\Parameters;

/**
 * Class FormUrlencoded
 */
class FormUrlencoded implements DeserializerInterface
{
    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * FormUrlencoded constructor.
     * @param Parameters $parameters
     */
    public function __construct(
        Parameters $parameters
    ) {
        $this->parameters = $parameters;
    }

    /**
     * Parse Request body into array of params.
     *
     * @param string $encodedBody Posted content from request.
     * @return array|null Return NULL if content is invalid.
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Webapi\Exception If decoding error was encountered.
     */
    public function deserialize($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" data type is invalid. String is expected.', gettype($encodedBody))
            );
        }

        $decodedBody = urldecode($encodedBody);
        $this->parameters->fromString($decodedBody);
        return $this->parameters->getArrayCopy();
    }
}
