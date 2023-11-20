<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

/**
 * Factory for instantiating XML parser objects
 *
 * We can't use auto-generated factories here because the Parser class is used as part of di:compile
 */
class ParserFactory
{
    public function create() : Parser
    {
        return new Parser;
    }
}
