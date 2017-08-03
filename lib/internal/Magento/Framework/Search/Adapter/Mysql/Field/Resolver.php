<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Field\Resolver
 *
 * @since 2.0.0
 */
class Resolver implements ResolverInterface
{
    /**
     * @var FieldFactory
     * @since 2.0.0
     */
    private $fieldFactory;

    /**
     * @param FieldFactory $fieldFactory
     * @since 2.0.0
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
