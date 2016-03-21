<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Widget;

/**
 * @magentoAppArea frontend
 */
class TemplateFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if all the declared widget templates actually exist
     *
     * @param string $class
     * @param string $template
     * @dataProvider widgetTemplatesDataProvider
     */
    public function testWidgetTemplates($class, $template)
    {
        /** @var $blockFactory \Magento\Framework\View\Element\BlockFactory */
        $blockFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Element\BlockFactory'
        );
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $blockFactory->createBlock($class);
        $this->assertInstanceOf('Magento\Framework\View\Element\Template', $block);
        $block->setTemplate((string)$template);
        $this->assertFileExists($block->getTemplateFile());
    }

    /**
     * Collect all declared widget blocks and templates
     *
     * @return array
     */
    public function widgetTemplatesDataProvider()
    {
        $result = [];
        /** @var $model \Magento\Widget\Model\Widget */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Widget\Model\Widget');
        foreach ($model->getWidgetsArray() as $row) {
            /** @var $instance \Magento\Widget\Model\Widget\Instance */
            $instance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Widget\Model\Widget\Instance'
            );
            $config = $instance->setType($row['type'])->getWidgetConfigAsArray();
            $class = $row['type'];
            if (is_subclass_of($class, 'Magento\Framework\View\Element\Template')) {
                if (isset(
                    $config['parameters']
                ) && isset(
                    $config['parameters']['template']
                ) && isset(
                    $config['parameters']['template']['values']
                )
                ) {
                    $templates = $config['parameters']['template']['values'];
                    foreach ($templates as $template) {
                        if (isset($template['value'])) {
                            $result[] = [$class, (string)$template['value']];
                        }
                    }
                }
            }
        }
        return $result;
    }
}
