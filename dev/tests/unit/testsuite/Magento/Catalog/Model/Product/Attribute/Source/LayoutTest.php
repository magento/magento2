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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Layout */
    protected $layoutModel;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Theme\Model\Layout\Source\Layout|\PHPUnit_Framework_MockObject_MockObject */
    protected $layoutSourceModel;

    protected function setUp()
    {
        $this->layoutSourceModel = $this->getMock(
            'Magento\Theme\Model\Layout\Source\Layout',
            array(
                'toOptionArray'
            ),
            array(),
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->layoutModel = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Attribute\Source\Layout',
            array(
                'pageSourceLayout' => $this->layoutSourceModel
            )
        );
    }

    public function testGetAllOptions()
    {
        $expectedOptions = array(
            '0' => array('value' => '', 'label' => 'No layout updates'),
            '1' => array('value' => 'option_value', 'label' => 'option_label')
        );
        $this->layoutSourceModel->expects($this->once())->method('toOptionArray')
            ->will($this->returnValue(array('0' => $expectedOptions['1'])));
        $layoutOptions = $this->layoutModel->getAllOptions();
        $this->assertEquals($expectedOptions, $layoutOptions);
    }
}
