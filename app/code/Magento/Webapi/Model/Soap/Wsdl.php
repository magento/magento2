<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Webapi\Model\Laminas\Soap\Wsdl as LaminasWsdl;
use Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;

/**
 * Magento-specific WSDL builder.
 */
class Wsdl extends LaminasWsdl
{
    /**
     * @inheritdoc
     */
    public function __construct($name, $uri, ComplexTypeStrategy $strategy)
    {
        parent::__construct($name, $uri, $strategy);
    }

    /**
     * @inheritdoc
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
