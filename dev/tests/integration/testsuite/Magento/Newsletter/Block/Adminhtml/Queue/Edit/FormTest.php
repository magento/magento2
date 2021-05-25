<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Block\Adminhtml\Queue\Edit;

use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Newsletter\Model\Queue;

/**
 * Test class for \Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $queue = $objectManager->get(Queue::class);
        /** @var \Magento\Framework\Registry $registry */
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        $registry->register('current_queue', $queue);

        $objectManager->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme();
        $objectManager->get(
            \Magento\Framework\Config\ScopeInterface::class
        )->setCurrentScope(
            FrontNameResolver::AREA_CODE
        );
        $block = $objectManager->create(
            \Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form::class,
            ['registry' => $registry]
        );
        $prepareFormMethod = new \ReflectionMethod(
            \Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form::class,
            '_prepareForm'
        );
        $prepareFormMethod->setAccessible(true);

        $statuses = [
            Queue::STATUS_NEVER,
            Queue::STATUS_PAUSE,
        ];
        foreach ($statuses as $status) {
            $queue->setQueueStatus($status);
            $prepareFormMethod->invoke($block);
            $element = $block->getForm()->getElement('date');
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getTimeFormat());
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
