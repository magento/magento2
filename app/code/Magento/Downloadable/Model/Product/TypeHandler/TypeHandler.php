<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeHandler;

use Magento\Catalog\Model\Product;

/**
 * Class TypeHandler
 * @since 2.0.0
 */
class TypeHandler implements TypeHandlerInterface
{

    /**
     * @var \Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface[]
     * @since 2.0.0
     */
    private $handlers;

    /**
     * @param \Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface[] $handlers
     * @since 2.0.0
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(Product $product, array $data)
    {
        foreach ($this->handlers as $handler) {
            $handler->save($product, $data);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isCanHandle(array $data)
    {
        $result = false;
        foreach ($this->handlers as $handler) {
            if ($handler->isCanHandle($data)) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}
