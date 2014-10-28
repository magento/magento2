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

namespace Magento\Framework\View;

/**
 * Test for view Context model
 */
class DataSourcePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataSourcePool
     */
    protected $dataSourcePool;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockFactory;

    protected function setUp()
    {
        $this->blockFactory = $this->getMockBuilder('Magento\Framework\View\Element\BlockFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->dataSourcePool = $objectManager->getObject('Magento\Framework\View\DataSourcePool', [
            'blockFactory' => $this->blockFactory
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid Data Source class name: NotExistingBlockClass
     */
    public function testAddWithException()
    {
        $this->dataSourcePool->add('DataSourcePoolTestBlock', 'NotExistingBlockClass');
    }

    protected function createBlock($blockClass)
    {
        $block = $this->getMock('Magento\Framework\View\Element\BlockInterface');

        $this->blockFactory->expects($this->once())
            ->method('createBlock')
            ->with($blockClass)
            ->will($this->returnValue($block));
        return $block;
    }

    public function testAdd()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = 'Magento\Framework\View\DataSourcePoolTestBlock';

        $block = $this->createBlock($blockClass);

        $this->assertSame($block, $this->dataSourcePool->add($blockName, $blockClass));
    }

    public function testGet()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = 'Magento\Framework\View\DataSourcePoolTestBlock';

        $block = $this->createBlock($blockClass);
        $this->dataSourcePool->add($blockName, $blockClass);

        $this->assertSame($block, $this->dataSourcePool->get($blockName));
        $this->assertEquals([$blockName => $block], $this->dataSourcePool->get());
        $this->assertNull($this->dataSourcePool->get('WrongName'));
    }

    public function testGetEmpty()
    {
        $this->assertEquals([], $this->dataSourcePool->get());
    }

    public function testAssignAndGetNamespaceData()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = 'Magento\Framework\View\DataSourcePoolTestBlock';

        $block = $this->createBlock($blockClass);
        $this->dataSourcePool->add($blockName, $blockClass);

        $namespace = 'namespace';
        $alias = 'alias';
        $this->dataSourcePool->assign($blockName, $namespace, $alias);

        $this->assertEquals(['alias' => $block], $this->dataSourcePool->getNamespaceData($namespace));
        $this->assertEquals([], $this->dataSourcePool->getNamespaceData('WrongNamespace'));
    }
}

/**
 * Class DataSourcePoolTestBlock mock
 */
class DataSourcePoolTestBlock implements \Magento\Framework\View\Element\BlockInterface
{
    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        return '';
    }
}
