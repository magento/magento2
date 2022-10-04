<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\FileSystem;

use Magento\Framework\Config\Reader\Filesystem;

class XmlReader extends Filesystem
{
    /**
     * Name of an attribute that stands for data type of node values
     */
    private const TYPE_ATTRIBUTE = 'xsi:type';

    /**
     * Create and return a config merger instance that takes into account types of arguments
     *
     * {@inheritdoc}
     */
    protected function _createConfigMerger($mergerClass, $initialContents)
    {
        return new $mergerClass(
            $initialContents,
            $this->validationState,
            $this->_idAttributes,
            self::TYPE_ATTRIBUTE,
            $this->_perFileSchema
        );
    }
}
