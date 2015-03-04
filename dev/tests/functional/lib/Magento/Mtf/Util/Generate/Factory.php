<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate;

use Magento\Framework\App;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * Factory classes generator
 *
 * @deprecated
 */
class Factory extends AbstractGenerate
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @constructor
     * @param ObjectManagerInterface $objectManager
     * @param Factory\Block $block
     * @param Factory\Fixture $fixture
     * @param Factory\Handler $handler
     * @param Factory\Page $page
     * @param Factory\Repository $repository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Factory\Block $block,
        Factory\Fixture $fixture,
        Factory\Handler $handler,
        Factory\Page $page,
        Factory\Repository $repository
    ) {
        $this->objectManager = $objectManager;
        $this->block = $block;
        $this->fixture = $fixture;
        $this->handler = $handler;
        $this->page = $page;
        $this->repository = $repository;
    }

    /**
     * Generate Handlers
     */
    public function launch()
    {
        $this->block->launch();
        $this->fixture->launch();
        $this->handler->launch();
        $this->page->launch();
        $this->repository->launch();

        return $this->objectManager->get('Magento\Framework\App\ResponseInterface');
    }

    /**
     * Generate single class.
     *
     * @param string $className
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate($className)
    {
        return false;
    }
}
