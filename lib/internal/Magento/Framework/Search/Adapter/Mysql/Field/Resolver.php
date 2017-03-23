<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

class Resolver implements ResolverInterface
{
    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param FieldFactory $fieldFactory
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $fields)
    {
        $resolvedFields = [];
        foreach ($fields as $field) {
            $resolvedFields[] = $this->fieldFactory->create(['column' => $field]);
        }

        return $resolvedFields;
    }
}
