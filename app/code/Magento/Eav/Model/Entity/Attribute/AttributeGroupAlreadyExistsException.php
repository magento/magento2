<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Class AlreadyExistsException
 */
class AttributeGroupAlreadyExistsException extends AlreadyExistsException
{
    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Attribute group with same code is already exist. Please enter other Group name');
        }
        parent::__construct($phrase, $cause);
    }
}
