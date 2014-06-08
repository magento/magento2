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
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $isAllowed
     * @param array $data
     * @param bool $expected
     * @dataProvider getNoDisplayDataProvider
     */
    public function testGetNoDisplay($isAllowed, $data, $expected)
    {
        $authorizationMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue($isAllowed));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var Category $element */
        $element = $objectManager->getObject(
            '\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
            ['authorization' => $authorizationMock, 'data' => $data]
        );

        $this->assertEquals($expected, $element->getNoDisplay());
    }

    public function getNoDisplayDataProvider()
    {
        return [
            [true, [], false],
            [false, [], true],
            [true, ['no_display' => false], false],
            [true, ['no_display' => true], true],
            [false, ['no_display' => false], true],
            [false, ['no_display' => true], true],
        ];
    }
}
