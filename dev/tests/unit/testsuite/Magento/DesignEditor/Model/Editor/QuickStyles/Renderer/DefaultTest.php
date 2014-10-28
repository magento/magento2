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

/**
 * Default renderer test
 */
namespace Magento\DesignEditor\Model\Editor\QuickStyles\Renderer;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\DefaultRenderer::toCss
     * @dataProvider colorPickerData
     */
    public function testToCss($expectedResult, $data)
    {
        $rendererModel = $this->getMock(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\DefaultRenderer',
            null,
            array(),
            '',
            false
        );

        $this->assertEquals($expectedResult, $rendererModel->toCss($data));
    }

    public function colorPickerData()
    {
        return array(
            array(
                'expected_result' => ".menu { color: red; }",
                'data' => array(
                    'type' => 'color-picker',
                    'default' => '#f8f8f8',
                    'selector' => '.menu',
                    'attribute' => 'color',
                    'value' => 'red'
                )
            )
        );
    }
}
