<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Block\System\Variable;

/**
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testConstruct()
    {
        $data = [
            'code' => 'test_variable_1',
            'name' => 'Test Variable 1',
            'html_value' => '<b>Test Variable 1 HTML Value</b>',
            'plain_value' => 'Test Variable 1 plain Value',
        ];
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $variable = $objectManager->create(\Magento\Variable\Model\Variable::class)->setData($data)->save();

        $objectManager->get(\Magento\Framework\Registry::class)->register('current_variable', $variable);
        $objectManager->get(
            \Magento\Framework\App\RequestInterface::class
        )->setParam('variable_id', $variable->getId());
        $block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Variable\Block\System\Variable\Edit::class,
            'variable'
        );
        $this->assertArrayHasKey('variable-delete_button', $block->getLayout()->getAllBlocks());
    }
}
