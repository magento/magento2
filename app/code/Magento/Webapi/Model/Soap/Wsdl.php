<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use DOMElement;
use Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;

/**
 * Magento-specific WSDL builder.
 */
class Wsdl extends \Zend\Soap\Wsdl
{
    /**
     * Constructor.
     * Save URI for targetNamespace generation.
     *
     * @param string $name
     * @param string|\Zend\Uri\Uri $uri
     * @param ComplexTypeStrategy $strategy
     */
    public function __construct($name, $uri, ComplexTypeStrategy $strategy)
    {
        parent::__construct($name, $uri, $strategy);
    }

    /**
     * Add an operation to port type.
     *
     * @param DOMElement $portType
     * @param string $name Operation name
     * @param string|bool $input Input Message
     * @param string|bool $output Output Message
     * @param string|bool|array $fault Message name OR array('message' => ..., 'name' => ...)
     * @return object The new operation's XML_Tree_Node
     */
    public function addPortOperation($portType, $name, $input = false, $output = false, $fault = false)
    {
        $operation = parent::addPortOperation($portType, $name, $input, $output, false);
        if (is_array($fault)) {
            $isMessageValid = isset(
                $fault['message']
            ) && is_string(
                $fault['message']
            ) && strlen(
                trim($fault['message'])
            );
            $isNameValid = isset($fault['name']) && is_string($fault['name']) && strlen(trim($fault['name']));

            if ($isNameValid && $isMessageValid) {
                $node = $this->toDomDocument()->createElement('fault');
                $node->setAttribute('name', $fault['name']);
                $node->setAttribute('message', $fault['message']);
                $operation->appendChild($node);
            }
        }
        return $operation;
    }
}
