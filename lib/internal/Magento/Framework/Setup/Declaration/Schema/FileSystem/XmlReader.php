<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @param string $mergerClass
     * @param string $initialContents
     * @return \Magento\Framework\Config\Dom
     * @throws \UnexpectedValueException
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
