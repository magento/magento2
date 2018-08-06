<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * PHP Code Sniffer CLI Wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

/**
 *
 * Class LessWrapper
 *
 * Class is used for adding less extension into "Runner->config->extensions".
 */
class LessWrapper extends Wrapper
{
    const LESS_FILE_EXTENSION = 'less';

    const LESS_TOKENIZER = 'CSS';

    /**
     * Init configuration for less file types
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->config->extensions += [self::LESS_FILE_EXTENSION => self::LESS_TOKENIZER];
    }
}
