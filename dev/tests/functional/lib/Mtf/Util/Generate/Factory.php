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

namespace Mtf\Util\Generate;

use Magento\Framework\ObjectManager;
use Magento\Framework\App;

/**
 * Class Factory
 * Factory classes generator
 *
 * @deprecated
 */
class Factory extends AbstractGenerate
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param Factory\Block $block
     * @param Factory\Fixture $fixture
     * @param Factory\Handler $handler
     * @param Factory\Page $page
     * @param Factory\Repository $repository
     */
    public function __construct(
        ObjectManager $objectManager,
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
}
