<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Filter;

use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Reports\Block\Adminhtml\Filter\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme();
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout::class
        );
        $block = $layout->addBlock(\Magento\Reports\Block\Adminhtml\Filter\Form::class);
        $prepareFormMethod = new \ReflectionMethod(\Magento\Reports\Block\Adminhtml\Filter\Form::class, '_prepareForm');
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (['from', 'to'] as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
