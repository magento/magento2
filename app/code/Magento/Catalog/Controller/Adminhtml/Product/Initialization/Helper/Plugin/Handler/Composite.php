<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function __construct(HandlerFactory $factory, array $handlers = array())
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
