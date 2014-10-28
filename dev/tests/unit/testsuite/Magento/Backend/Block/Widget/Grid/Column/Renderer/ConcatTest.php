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
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Object;

class ConcatTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;
    /** @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Concat */
    protected $renderer;

    public function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->renderer = $this->objectManagerHelper->getObject(
            'Magento\\Backend\\Block\\Widget\\Grid\\Column\\Renderer\\Concat'
        );
    }

    /**
     * @return array
     */
    public function typeProvider()
    {
        return [
            ['getGetter', ['getTest', 'getBest']],
            ['getIndex', ['test', 'best', 'nothing']],
        ];
    }

    /**
     * @dataProvider typeProvider
     */
    public function testRender($method, $getters)
    {
        $object = new Object(['test' => 'a', 'best' => 'b']);
        $column = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods([$method, 'getSeparator'])
            ->getMock();
        $column->expects($this->any())
            ->method('getSeparator')
            ->willReturn('-');
        $column->expects($this->any())
            ->method($method)
            ->willReturn($getters);
        $column->expects($this->any())
            ->method('getGetter')
            ->willReturn(['getTest', 'getBest']);
        $this->renderer->setColumn($column);
        $this->assertEquals('a-b', $this->renderer->render($object));
    }
}