<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Declaration;

use Magento\Framework\Config\ReaderInterface;

/**
 * Read schema from different places: XML, csv, etc
 * You can add one more reader from di.xml
 * Note: that schema from your reader will not be validated through XSD
 */
class ReaderComposite implements ReaderInterface
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * @inheritdoc
     */
    public function read($scope = null)
    {
        $schema = ['table' => []];
        foreach ($this->readers as $reader) {
            $schema = array_replace_recursive($schema, $reader->read($scope));
        }

        return $schema;
    }
}
