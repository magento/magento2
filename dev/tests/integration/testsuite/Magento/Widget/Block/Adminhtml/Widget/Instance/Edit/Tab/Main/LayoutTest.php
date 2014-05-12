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
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main;

/**
 * @magentoAppArea adminhtml
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout',
            '',
            array(
                'data' => array(
                    'widget_instance' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                        'Magento\Widget\Model\Widget\Instance'
                    )
                )
            )
        );
        $this->_block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface')
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetLayoutsChooser()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\State'
        )->setAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme();

        $actualHtml = $this->_block->getLayoutsChooser();
        $this->assertStringStartsWith('<select ', $actualHtml);
        $this->assertStringEndsWith('</select>', $actualHtml);
        $this->assertContains('id="layout_handle"', $actualHtml);
        $optionCount = substr_count($actualHtml, '<option ');
        $this->assertGreaterThan(1, $optionCount, 'HTML select tag must provide options to choose from.');
        $this->assertEquals($optionCount, substr_count($actualHtml, '</option>'));
    }
}
