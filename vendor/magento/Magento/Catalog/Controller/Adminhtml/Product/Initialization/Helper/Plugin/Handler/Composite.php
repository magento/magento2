<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface;
use Magento\Catalog\Model\Product;

class Composite implements HandlerInterface
{
    /**
     * Array of handler interface objects
     *
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @param HandlerFactory $factory
     * @param array $handlers
     */
    public function __construct(HandlerFactory $factory, array $handlers = [])
    {
        foreach ($handlers as $instance) {
            $this->handlers[] = $factory->create($instance);
        }
    }

    /**
     * Process each of the handler objects
     *
     * @param Product $product
     * @return void
     */
    public function handle(Product $product)
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($product);
        }
    }
}
