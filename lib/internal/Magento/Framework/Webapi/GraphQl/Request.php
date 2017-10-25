<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi\GraphQl;

use Magento\Framework\Phrase;

class Request extends \Magento\Framework\Webapi\Request
{
    /**#@+
     * HTTP methods supported by GraphQl.
     */
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    /**#@-*/

    /**
     * Character set which must be used in request.
     */
    const REQUEST_CHARSET = 'utf-8';

    /**
     * @var \Magento\Framework\Webapi\Rest\Request\DeserializerFactory
     */
    private $deserializerFactory;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request\DeserializerInterface
     */
    private $deserializer;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Stdlib\Cookie\CookieReaderInterface $cookieReader
     * @param \Magento\Framework\Stdlib\StringUtils $converter
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Webapi\Rest\Request\DeserializerFactory $deserializerFactory
     * @param null|string $uri
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\CookieReaderInterface $cookieReader,
        \Magento\Framework\Stdlib\StringUtils $converter,
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Webapi\Rest\Request\DeserializerFactory $deserializerFactory,
        $uri = null
    ) {
        parent::__construct($cookieReader, $converter, $areaList, $configScope, $uri);
        $this->deserializerFactory = $deserializerFactory;
    }

    /**
     * Fetch data from HTTP Request body.
     *
     * @return array
     */
    public function getBodyParams()
    {
        if (null == $this->_bodyParams) {
            $this->_bodyParams = [];
            //avoid JSON decoding with empty string
            if ($this->getContent()) {
                $this->_bodyParams = (array)$this->getDeserializer()->deserialize((string)$this->getContent());
            }
        }
        return $this->_bodyParams;
    }

    /**
     * Get Content-Type of request.
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getContentType()
    {
        $headerValue = $this->getHeader('Content-Type');

        if (!$headerValue) {
            throw new \Magento\Framework\Exception\InputException(new Phrase('Content-Type header is empty.'));
        }
        if (!preg_match('~^([a-z\d/\-+.]+)(?:; *charset=(.+))?$~Ui', $headerValue, $matches)) {
            throw new \Magento\Framework\Exception\InputException(new Phrase('Content-Type header is invalid.'));
        }
        // request encoding check if it is specified in header
        if (isset($matches[2]) && self::REQUEST_CHARSET != strtolower($matches[2])) {
            throw new \Magento\Framework\Exception\InputException(new Phrase('UTF-8 is the only supported charset.'));
        }

        return $matches[1];
    }

    /**
     * Get request deserializer.
     *
     * @return \Magento\Framework\Webapi\Rest\Request\DeserializerInterface
     */
    private function getDeserializer()
    {
        if (null === $this->_deserializer) {
            $this->deserializer = $this->deserializerFactory->get($this->getContentType());
        }
        return $this->deserializer;
    }
}
