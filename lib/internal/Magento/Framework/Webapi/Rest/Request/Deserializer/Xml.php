<?php
/**
 * XML deserializer of REST request content.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest\Request\Deserializer;

use Magento\Framework\App\State;
use Magento\Framework\Phrase;

/**
 * Class \Magento\Framework\Webapi\Rest\Request\Deserializer\Xml
 *
 */
class Xml implements \Magento\Framework\Webapi\Rest\Request\DeserializerInterface
{
    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $_xmlParser;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\Xml\Parser $xmlParser
     * @param State $appState
     */
    public function __construct(\Magento\Framework\Xml\Parser $xmlParser, State $appState)
    {
        $this->_xmlParser = $xmlParser;
        $this->_appState = $appState;
    }

    /**
     * Load error string.
     *
     * Is null if there was no error while loading
     *
     * @var string
     */
    protected $_errorMessage = null;

    /**
     * Convert XML document into array.
     *
     * @param string $xmlRequestBody XML document
     * @return array Data converted from XML document to array. Root node is excluded from response.
     * @throws \InvalidArgumentException In case of invalid argument type.
     * @throws \Magento\Framework\Webapi\Exception If decoding error occurs.
     */
    public function deserialize($xmlRequestBody)
    {
        if (!is_string($xmlRequestBody)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" data type is invalid. String is expected.', gettype($xmlRequestBody))
            );
        }
        /** Disable external entity loading to prevent possible vulnerability */
        $previousLoaderState = libxml_disable_entity_loader(true);
        set_error_handler([$this, 'handleErrors']);

        $this->_xmlParser->loadXML($xmlRequestBody);

        restore_error_handler();
        libxml_disable_entity_loader($previousLoaderState);

        /** Process errors during XML parsing. */
        if ($this->_errorMessage !== null) {
            if ($this->_appState->getMode() !== State::MODE_DEVELOPER) {
                $exceptionMessage = new Phrase('Decoding error.');
            } else {
                $exceptionMessage = new Phrase('Decoding Error: %1', [$this->_errorMessage]);
            }
            throw new \Magento\Framework\Webapi\Exception($exceptionMessage);
        }
        $data = $this->_xmlParser->xmlToArray();
        /** Data will always have exactly one element so it is safe to call reset here. */
        return reset($data);
    }

    /**
     * Handle any errors during XML loading.
     *
     * @param integer $errorNumber
     * @param string $errorMessage
     * @param string $errorFile
     * @param integer $errorLine
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleErrors($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        if ($this->_errorMessage === null) {
            $this->_errorMessage = $errorMessage;
        } else {
            $this->_errorMessage .= $errorMessage;
        }
    }
}
