<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap;

use DOMElement;

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
     * @param \Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy\AnyComplexType $strategy
     */
    public function __construct(
        $name,
        $uri,
        \Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy\AnyComplexType $strategy
    ) {
        parent::__construct($name, $uri, $strategy);
    }

    /**
     * Add complex type definition
     *
     * @param \DOMNode $complexTypeNode XSD of service method for input/output
     * @return string|null
     */
    public function addComplexType($complexTypeNode)
    {
        $this->addSchemaTypeSection();

        $strategy = $this->getComplexTypeStrategy();
        $strategy->setContext($this);
        // delegates the detection of a complex type to the current strategy
        return $strategy->addComplexType($complexTypeNode);
    }

    /**
     * Add an operation to port type.
     *
     * Multiple faults generation is allowed, while it is not allowed in parent.
     *
     * @param DOMElement $portType
     * @param string $name Operation name
     * @param string|bool $input Input Message
     * @param string|bool $output Output Message
     * @param array|bool $fault array of Fault messages in the format: array(array('message' => ..., 'name' => ...))
     * @return object The new operation's XML_Tree_Node
     */
    public function addPortOperation($portType, $name, $input = false, $output = false, $fault = false)
    {
        $operation = parent::addPortOperation($portType, $name, $input, $output, false);
        if (is_array($fault)) {
            foreach ($fault as $faultInfo) {
                $isMessageValid = isset($faultInfo['message']) && is_string($faultInfo['message'])
                    && strlen(trim($faultInfo['message']));
                $isNameValid = isset($faultInfo['name']) && is_string($faultInfo['name'])
                    && strlen(trim($faultInfo['name']));

                if ($isNameValid && $isMessageValid) {
                    $node = $this->toDomDocument()->createElement('fault');
                    $node->setAttribute('name', $faultInfo['name']);
                    $node->setAttribute('message', $faultInfo['message']);
                    $operation->appendChild($node);
                }
            }
        }
        return $operation;
    }

    /**
     * Add an operation to a binding element.
     *
     * Multiple faults binding is allowed, while it is not allowed in parent.
     *
     * @param DOMElement $binding
     * @param string $name Operation name
     * @param bool|array $input An array of attributes for the input element,
     *      allowed keys are: 'use', 'namespace', 'encodingStyle'.
     * @param bool|array $output An array of attributes for the output element,
     *      allowed keys are: 'use', 'namespace', 'encodingStyle'.
     * @param bool|array $fault An array of arrays which contain fault names: array(array('name' => ...))).
     * @param int $soapVersion SOAP version to be used in binding operation. 1.1 used by default.
     * @return DOMElement The new Operation's XML_Tree_Node
     */
    public function addBindingOperation(
        $binding,
        $name,
        $input = false,
        $output = false,
        $fault = false,
        $soapVersion = SOAP_1_1
    ) {
        $operation = parent::addBindingOperation($binding, $name, $input, $output, false, $soapVersion);
        if (is_array($fault)) {
            foreach ($fault as $faultInfo) {
                $isNameValid = isset($faultInfo['name']) && is_string($faultInfo['name'])
                    && strlen(trim($faultInfo['name']));

                if ($isNameValid) {
                    $faultInfo['use'] = 'literal';
                    $wsdlFault = $this->toDomDocument()->createElement('fault');
                    $wsdlFault->setAttribute('name', $faultInfo['name']);

                    $soapFault = $this->toDomDocument()->createElement('soap:fault');
                    $soapFault->setAttribute('name', $faultInfo['name']);
                    $soapFault->setAttribute('use', 'literal');

                    $wsdlFault->appendChild($soapFault);
                    $operation->appendChild($wsdlFault);
                }
            }
        }
        return $operation;
    }
}
