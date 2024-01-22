<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Xml;

/**
 * Factory for instantiating XML parser objects
 *
 * We can't use auto-generated factories here because the Parser class is used as part of di:compile
 */
class ParserFactory
{
    /**
     * Creates a new Parser
     *
     * @return Parser
     */
    public function create() : Parser
    {
        return new Parser;
    }
}
