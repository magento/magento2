<?php
/**
 * Unit Test for \Magento\Filesystem\Stream\Mode
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Stream;

class ModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider modesDataProvider
     * @param string $mode
     * @param string $base
     * @param bool $hasPlus
     * @param string $flag
     */
    public function testConstructor($mode, $base, $hasPlus, $flag)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertAttributeEquals($base, '_base', $streamMode);
        $this->assertAttributeEquals($hasPlus, '_plus', $streamMode);
        $this->assertAttributeEquals($flag, '_flag', $streamMode);
        $this->assertEquals($mode, $streamMode->getMode());
    }

    /**
     * @return array
     */
    public function modesDataProvider()
    {
        return array(
            array('r', 'r', false, null),
            array('rb', 'r', false, 'b'),
            array('r+', 'r', true, null),
            array('r+b', 'r', true, 'b'),
            array('w', 'w', false, null),
            array('wb', 'w', false, 'b'),
            array('w+', 'w', true, null),
            array('w+b', 'w', true, 'b'),
            array('a', 'a', false, null),
            array('ab', 'a', false, 'b'),
            array('a+', 'a', true, null),
            array('a+b', 'a', true, 'b'),
            array('x', 'x', false, null),
            array('xb', 'x', false, 'b'),
            array('x+', 'x', true, null),
            array('x+b', 'x', true, 'b'),
            array('c', 'c', false, null),
            array('cb', 'c', false, 'b'),
            array('c+', 'c', true, null),
            array('c+b', 'c', true, 'b'),
        );
    }

    /**
     * @dataProvider rModesDataProvider
     * @param string $mode
     */
    public function testAllowRead($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isReadAllowed());
    }

    /**
     * @return array
     */
    public function rModesDataProvider()
    {
        return array(array('r'), array('rb')) + $this->plusModesDataProvider();
    }

    /**
     * @dataProvider wModesDataProvider
     * @param string $mode
     */
    public function testAllowsWrite($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isWriteAllowed());
    }

    /**
     * @return array
     */
    public function wModesDataProvider()
    {
        return array(
            array('w'), array('wb'),
            array('a'), array('ab'),
            array('x'), array('xb'),
            array('c'), array('cb'),
        ) + $this->plusModesDataProvider();
    }

    /**
     * @dataProvider nonXModesDataProvider
     * @param string $mode
     */
    public function testAllowsExistingFileOpening($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isExistingFileOpenAllowed());
    }

    /**
     * @return array
     */
    public function nonXModesDataProvider()
    {
        return array(
            array('r'), array('rb'),
            array('w'), array('wb'),
            array('a'), array('ab'),
            array('c'), array('cb'),
            array('r+'), array('r+b'),
            array('w+'), array('w+b'),
            array('a+'), array('a+b'),
            array('c+'), array('c+b'),
        );
    }

    /**
     * @dataProvider nonRModesDataProvider
     * @param string $mode
     */
    public function testAllowsNewFileOpening($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isNewFileOpenAllowed());
    }

    /**
     * @return array
     */
    public function nonRModesDataProvider()
    {
        return array(
            array('x'), array('xb'),
            array('w'), array('wb'),
            array('a'), array('ab'),
            array('c'), array('cb'),
            array('w+'), array('w+b'),
            array('a+'), array('a+b'),
            array('c+'), array('c+b'),
        );
    }

    /**
     * @dataProvider onlyWModesDataProvider
     * @param string $mode
     */
    public function testImpliesExistingContentDeletion($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isExistingContentDeletionImplied());
    }

    /**
     * @return array
     */
    public function onlyWModesDataProvider()
    {
        return array(
            array('w'), array('wb'),
            array('w+'), array('w+b'),
        );
    }

    /**
     * @dataProvider nonAModesDataProvider
     * @param string $mode
     */
    public function testImpliesPositioningCursorAtTheBeginning($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isPositioningCursorAtTheBeginningImplied());
    }

    /**
     * @return array
     */
    public function nonAModesDataProvider()
    {
        return array(
            array('r'), array('rb'),
            array('x'), array('xb'),
            array('w'), array('wb'),
            array('c'), array('cb'),
            array('w+'), array('w+b'),
            array('r+'), array('r+b'),
            array('c+'), array('c+b'),
        );
    }

    /**
     * @dataProvider onlyAModesDataProvider
     * @param string $mode
     */
    public function testImpliesPositioningCursorAtTheEnd($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isPositioningCursorAtTheEndImplied());
    }

    /**
     * @return array
     */
    public function onlyAModesDataProvider()
    {
        return array(
            array('a'), array('ab'),
            array('a+'), array('a+b'),
        );
    }

    /**
     * @dataProvider onlyBModesDataProvider
     * @param string $mode
     */
    public function testIsBinary($mode)
    {
        $streamMode = new \Magento\Filesystem\Stream\Mode($mode);
        $this->assertTrue($streamMode->isBinary());
    }

    /**
     * @return array
     */
    public function onlyBModesDataProvider()
    {
        return array(
            array('rb'), array('r+b'),
            array('ab'), array('a+b'),
            array('wb'), array('w+b'),
            array('xb'), array('x+b'),
            array('cb'), array('c+b'),
        );
    }


    /**
     * @return array
     */
    public function plusModesDataProvider()
    {
        return array(
            array('r+'), array('r+b'),
            array('w+'), array('w+b'),
            array('a+'), array('a+b'),
            array('x+'), array('x+b'),
            array('c+'), array('c+b'),
        );
    }
}
