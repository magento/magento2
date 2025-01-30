<?php
/**
 * Set of tests of layout directives handling behavior
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View;

use Magento\Framework\App\State;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutDirectivesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     */
    protected $builderFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->layoutFactory = $this->objectManager->get(\Magento\Framework\View\LayoutFactory::class);
        $this->state = $this->objectManager->get(\Magento\Framework\App\State::class);
    }

    /**
     * Prepare a layout model with pre-loaded fixture of an update XML
     *
     * @param string $fixtureFile
     * @return \Magento\Framework\View\LayoutInterface
     */
    protected function _getLayoutModel($fixtureFile)
    {
        $this->objectManager->get(\Magento\Framework\App\Cache\Type\Layout::class)->clean();
        $layout = $this->layoutFactory->create();
        /** @var $xml \Magento\Framework\View\Layout\Element */
        $xml = simplexml_load_file(
            __DIR__ . "/_files/layout_directives_test/{$fixtureFile}",
            \Magento\Framework\View\Layout\Element::class
        );
        $layout->loadString($xml->asXml());
        $layout->generateElements();

        return $layout;
    }

    /**
     * Test scheduled operations in the rendering of elements
     *
     * Expected behavior:
     * 1) block1 was not declared at the moment when "1" invocation declared. The operation is scheduled
     * 2) block1 creation directive schedules adding "2" as well
     * 3) block2 is generated with "3"
     * 4) yet another action schedules replacing value of block2 into "4"
     * 5) when entire layout is read, all scheduled operations are executed in the same order as declared
     *    (blocks are instantiated first, of course)
     * The end result can be observed in container1
     *
     * @magentoAppIsolation enabled
     */
    public function testRenderElement()
    {
        $layout = $this->_getLayoutModel('render.xml');
        $this->assertEquals('124', $layout->renderElement('container_one'));
        $this->assertEquals('12', $layout->renderElement('block_one'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRenderNonExistentElementShouldThrowException()
    {
        $this->expectException(\OutOfBoundsException::class);

        $layout = $this->_getLayoutModel('render.xml');
        $this->assertEmpty($layout->renderElement('nonexisting_element'));

        $this->expectExceptionMessage(
            'The element with the "nonexisting_element" ID wasn\'t found. Verify the ID and try again.'
        );
    }

    /**
     * Invoke getBlock() while layout is being generated
     *
     * Assertions in this test are pure formalism. The point is to emulate situation where block refers to other block
     * while the latter hasn't been generated yet, and assure that there is no crash
     */
    public function testGetBlockUnscheduled()
    {
        $layout = $this->_getLayoutModel('get_block.xml');
        $this->assertInstanceOf(\Magento\Framework\View\Element\Text::class, $layout->getBlock('block_first'));
        $this->assertInstanceOf(\Magento\Framework\View\Element\Text::class, $layout->getBlock('block_second'));
    }

    public function testLayoutArgumentsDirective()
    {
        $layout = $this->_getLayoutModel('arguments.xml');
        $this->assertEquals('1', $layout->getBlock('block_with_args')->getOne());
        $this->assertEquals('two', $layout->getBlock('block_with_args')->getTwo());
        $this->assertEquals('3', $layout->getBlock('block_with_args')->getThree());
    }

    public function testLayoutArgumentsDirectiveIfComplexValues()
    {
        $layout = $this->_getLayoutModel('arguments_complex_values.xml');
        $this->assertEquals(
            ['parameters' => ['first' => '1', 'second' => '2']],
            $layout->getBlock('block_with_args_complex_values')->getOne()
        );
        $this->assertEquals('two', $layout->getBlock('block_with_args_complex_values')->getTwo());
        $this->assertEquals(
            ['extra' => ['key1' => 'value1', 'key2' => 'value2']],
            $layout->getBlock('block_with_args_complex_values')->getThree()
        );
    }

    public function testLayoutObjectArgumentsDirective()
    {
        $layout = $this->_getLayoutModel('arguments_object_type.xml');
        $this->assertInstanceOf(
            \Magento\Framework\Data\Collection::class,
            $layout->getBlock('block_with_object_args')->getOne()
        );
        $this->assertInstanceOf(
            \Magento\Framework\Data\Collection::class,
            $layout->getBlock('block_with_object_args')->getTwo()
        );
        $this->assertEquals(3, $layout->getBlock('block_with_object_args')->getThree());
    }

    public function testLayoutUrlArgumentsDirective()
    {
        $layout = $this->_getLayoutModel('arguments_url_type.xml');
        $this->assertStringContainsString('customer/account/login', $layout->getBlock('block_with_url_args')->getOne());
        $this->assertStringContainsString(
            'customer/account/logout',
            $layout->getBlock('block_with_url_args')->getTwo()
        );
        $this->assertStringContainsString('customer_id/3', $layout->getBlock('block_with_url_args')->getTwo());
    }

    public function testLayoutObjectArgumentUpdatersDirective()
    {
        $this->markTestSkipped('Will be fixed after MAGETWO-33840 will be done');
        $layout = $this->_getLayoutModel('arguments_object_type_updaters.xml');

        $expectedObjectData = [0 => 'updater call', 1 => 'updater call'];

        $expectedSimpleData = 1;

        $dataSource = $layout->getBlock('block_with_object_updater_args')->getOne();
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $dataSource);
        $this->assertEquals($expectedObjectData, $dataSource->getUpdaterCall());
        $this->assertEquals($expectedSimpleData, $layout->getBlock('block_with_object_updater_args')->getTwo());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMoveSameAlias()
    {
        $layout = $this->_getLayoutModel('move_the_same_alias.xml');
        $this->assertEquals('container1', $layout->getParentName('no_name3'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMoveNewAlias()
    {
        $layout = $this->_getLayoutModel('move_new_alias.xml');
        $this->assertEquals('new_alias', $layout->getElementAlias('no_name3'));
    }

    public function testActionAnonymousParentBlock()
    {
        $layout = $this->_getLayoutModel('action_for_anonymous_parent_block.xml');
        $this->assertEquals('schedule_block0', $layout->getParentName('test.block.insert'));
        $this->assertEquals('schedule_block1', $layout->getParentName('test.block.append'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRemove()
    {
        $layout = $this->_getLayoutModel('remove.xml');
        $this->assertFalse($layout->getBlock('no_name2'));
        $this->assertFalse($layout->getBlock('child_block1'));
        $this->assertTrue($layout->isBlock('child_block2'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRemoveCancellation()
    {
        $layout = $this->_getLayoutModel('remove_cancellation.xml');
        $this->assertTrue($layout->isContainer('container1'));
        $this->assertTrue($layout->isBlock('child_block1'));
        $this->assertTrue($layout->isBlock('no_name2'));
        $this->assertFalse($layout->getBlock('not_exist'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMove()
    {
        $layout = $this->_getLayoutModel('move.xml');
        $this->assertEquals('container2', $layout->getParentName('container1'));
        $this->assertEquals('container1', $layout->getParentName('no.name2'));
        $this->assertEquals('block_container', $layout->getParentName('no_name3'));

        // verify `after` attribute
        $this->assertEquals('block_container', $layout->getParentName('no_name'));
        $childrenOrderArray = array_keys($layout->getChildBlocks($layout->getParentName('no_name')));
        $positionAfter = array_search('child_block1', $childrenOrderArray);
        $positionToVerify = array_search('no_name', $childrenOrderArray);
        $this->assertEquals($positionAfter, --$positionToVerify);

        // verify `before` attribute
        $this->assertEquals('block_container', $layout->getParentName('no_name4'));
        $childrenOrderArray = array_keys($layout->getChildBlocks($layout->getParentName('no_name4')));
        $positionBefore = array_search('child_block2', $childrenOrderArray);
        $positionToVerify = array_search('no_name4', $childrenOrderArray);
        $this->assertEquals($positionBefore, ++$positionToVerify);
    }

    /**
     */
    public function testMoveBroken()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->_getLayoutModel('move_broken.xml');
    }

    /**
     */
    public function testMoveAliasBroken()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->_getLayoutModel('move_alias_broken.xml');
    }

    public function testRemoveBroken()
    {
        if ($this->state->getMode() === State::MODE_DEVELOPER) {
            $this->expectException('OutOfBoundsException');
        }
        $this->_getLayoutModel('remove_broken.xml');
    }

    /**
     * @param string $case
     * @param string $expectedResult
     * @dataProvider sortSpecialCasesDataProvider
     * @magentoAppIsolation enabled
     */
    public function testSortSpecialCases($case, $expectedResult)
    {
        $layout = $this->_getLayoutModel($case);
        $this->assertEquals($expectedResult, $layout->renderElement('root'));
    }

    /**
     * @return array
     */
    public static function sortSpecialCasesDataProvider()
    {
        return [
            'Before element which is after' => ['sort_before_after.xml', '312'],
            'Before element which is previous' => ['sort_before_before.xml', '213'],
            'After element which is after' => ['sort_after_after.xml', '312'],
            'After element which is previous' => ['sort_after_previous.xml', '321']
        ];
    }

    /**
     * @magentoConfigFixture current_store true_options 1
     * @magentoAppIsolation enabled
     */
    public function testIfConfigForBlock()
    {
        $layout = $this->_getLayoutModel('ifconfig.xml');
        $this->assertFalse($layout->getBlock('block1'));
        $this->assertFalse($layout->getBlock('block2'));
        $this->assertInstanceOf(\Magento\Framework\View\Element\BlockInterface::class, $layout->getBlock('block3'));
        $this->assertFalse($layout->getBlock('block4'));
    }

    /**
     * @magentoConfigFixture current_store true_options 1
     * @magentoAppIsolation enabled
     */
    public function testBlockGroups()
    {
        $layout = $this->_getLayoutModel('group.xml');
        $childNames = $layout->getBlock('block1')->getGroupChildNames('group1');
        $this->assertEquals(['block2', 'block3'], $childNames);
    }
}
