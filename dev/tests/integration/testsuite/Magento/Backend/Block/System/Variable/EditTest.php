<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\System\Variable;

/**
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
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
        $variable = $objectManager->create('Magento\Core\Model\Variable')->setData($data)->save();

        $objectManager->get('Magento\Framework\Registry')->register('current_variable', $variable);
        $objectManager->get('Magento\Framework\App\RequestInterface')->setParam('variable_id', $variable->getId());
        $block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\System\Variable\Edit',
            'variable'
        );
        $this->assertArrayHasKey('variable-delete_button', $block->getLayout()->getAllBlocks());
    }
}
