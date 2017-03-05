<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeHandler;

use Magento\Catalog\Model\Product;

/**
 * Class TypeHandler
 */
class TypeHandler implements TypeHandlerInterface
{

    /**
     * @var \Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface[]
     */
    private $handlers;

    /**
     * @param \Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Product $product, array $data)
    {
        foreach ($this->handlers as $handler) {
            $handler->save($product, $data);
        }
    }

    /**
     * {@inheritdoc}
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
