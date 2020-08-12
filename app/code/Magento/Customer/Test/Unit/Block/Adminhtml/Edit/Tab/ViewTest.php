<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Block\Adminhtml\Edit\Tab\View;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Edit\Tab\View
     */
    protected $view;

    protected function setUp(): void
    {
        $registry = $this->createMock(Registry::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $objectManagerHelper->getObject(
            View::class,
            [
                'registry' => $registry
            ]
        );
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('Customer View', $this->view->getTabLabel());
    }

    public function testGetTabTitle()
    {
        $this->assertEquals('Customer View', $this->view->getTabTitle());
    }
}
