<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader;

class TypeReader implements TypeMetaReaderInterface
{
    /** @var TypeMetaReaderInterface[] */
    private $typeReaders = [];

    /**
     * @param array $typeReaders
     */
    public function __construct(
        $typeReaders = []
    ) {
        $this->typeReaders = $typeReaders;
    }

    /**
     * {@inheritdoc}
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : ?array
    {
        foreach ($this->typeReaders as $typeReader) {
            $result = $typeReader->read($typeMeta);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }
}
