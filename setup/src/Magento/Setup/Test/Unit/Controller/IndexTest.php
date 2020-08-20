<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testIndexAction()
    {
        /** @var Index $controller */
        $controller = new Index();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertFalse($viewModel->terminate());
    }
}
