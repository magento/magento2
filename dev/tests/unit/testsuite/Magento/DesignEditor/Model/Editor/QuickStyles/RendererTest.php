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
 * Theme css file model class
 */
namespace Magento\DesignEditor\Model\Editor\QuickStyles;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider sampleData
     */
    public function testRender($expectedResult, $data)
    {
        /** @var $rendererModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer */
        $rendererModel = $this->getMock(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer',
            null,
            array(),
            '',
            false
        );

        $objectManager = $this->getMock('Magento\Framework\Object', array('get', 'toCss'), array(), '', false);

        $objectManager->expects($this->exactly(4))->method('get')->will($this->returnValue($objectManager));

        $objectManager->expects($this->exactly(4))->method('toCss')->will($this->returnValue('css_string'));

        $property = new \ReflectionProperty($rendererModel, '_quickStyleFactory');
        $property->setAccessible(true);
        $property->setValue($rendererModel, $objectManager);

        $this->assertEquals($expectedResult, $rendererModel->render($data));
    }

    /**
     * @return array
     */
    public function sampleData()
    {
        return array(
            array(
                'expected_result' => "css_string\ncss_string\ncss_string\ncss_string\n",
                'data' => array(
                    'header-background' => array(
                        'type' => 'background',
                        'components' => array(
                            'header-background:color-picker' => array(
                                'type' => 'color-picker',
                                'default' => 'transparent',
                                'selector' => '.header',
                                'attribute' => 'background-color',
                                'value' => '#FFFFFF'
                            ),
                            'header-background:background-uploader' => array(
                                'type' => 'background-uploader',
                                'components' => array(
                                    'header-background:image-uploader' => array(
                                        'type' => 'image-uploader',
                                        'default' => 'bg.gif',
                                        'selector' => '.header',
                                        'attribute' => 'background-image',
                                        'value' => '../image.jpg'
                                    ),
                                    'header-background:tile' => array(
                                        'type' => 'checkbox',
                                        'default' => 'no-repeat',
                                        'options' => array('no-repeat', 'repeat', 'repeat-x', 'repeat-y', 'inherit'),
                                        'selector' => '.header',
                                        'attribute' => 'background-repeat',
                                        'value' => 'checked'
                                    )
                                )
                            )
                        )
                    ),
                    'menu-background' => array(
                        'type' => 'color-picker',
                        'default' => '#f8f8f8',
                        'selector' => '.menu',
                        'attribute' => 'color',
                        'value' => '#000000'
                    )
                )
            )
        );
    }
}
