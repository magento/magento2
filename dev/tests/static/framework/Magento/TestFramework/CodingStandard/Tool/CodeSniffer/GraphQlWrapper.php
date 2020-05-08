<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

/**
 * Add GraphQl files extension to config.
 */
class GraphQlWrapper extends Wrapper
{
    const FILE_EXTENSION = 'graphqls';

    private const TOKENIZER = 'GraphQL';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->config->extensions += [self::FILE_EXTENSION => self::TOKENIZER];
    }
}
