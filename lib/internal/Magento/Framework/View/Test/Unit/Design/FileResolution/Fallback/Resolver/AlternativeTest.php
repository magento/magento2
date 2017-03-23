<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback\Resolver;

use \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Alternative;

use Magento\Framework\App\Filesystem\DirectoryList;

class AlternativeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directory;

    /**
     * @var \Magento\Framework\View\Design\Fallback\Rule\RuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rule;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple
     */
    private $object;

    protected function setUp()
    {
        $this->directory = $this->getMock(\Magento\Framework\Filesystem\Directory\Read::class, [], [], '', false);
        $readFactory = $this->getMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class, [], [], '', false);
        $readFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->directory));
        $this->rule = $this->getMock(
            \Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class,
            [],
            [],
            '',
            false
        );
        $rulePool = $this->getMock(\Magento\Framework\View\Design\Fallback\RulePool::class, [], [], '', false);
        $rulePool->expects($this->any())
            ->method('getRule')
            ->with('type')
            ->will($this->returnValue($this->rule));
        $this->object = new Alternative($readFactory, $rulePool, ['css' => ['less']]);
    }

    /**
     * @param array $alternativeExtensions
     *
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException(array $alternativeExtensions)
    {
        $this->setExpectedException('\InvalidArgumentException', "\$alternativeExtensions must be an array with format:"
            . " array('ext1' => array('ext1', 'ext2'), 'ext3' => array(...)]");

        $readFactory = $this->getMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class, [], [], '', false);
        $rulePool = $this->getMock(\Magento\Framework\View\Design\Fallback\RulePool::class, [], [], '', false);
        new Alternative($readFactory, $rulePool, $alternativeExtensions);
    }

    /**
     * @return array
     */
    public function constructorExceptionDataProvider()
    {
        return [
            'numerical keys'   => [['css', 'less']],
            'non-array values' => [['css' => 'less']],
        ];
    }

    public function testResolve()
    {
        $requestedFile = 'file.css';
        $expected = 'some/dir/file.less';

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects($this->any())
            ->method('getFullPath')
            ->will($this->returnValue('magento_theme'));
        $this->rule->expects($this->atLeastOnce())
            ->method('getPatternDirs')
            ->will($this->returnValue(['some/dir']));

        $fileExistsMap = [
            ['file.css', false],
            ['file.less', true],
        ];
        $this->directory->expects($this->any())
            ->method('isExist')
            ->will($this->returnValueMap($fileExistsMap));

        $actual = $this->object->resolve('type', $requestedFile, 'frontend', $theme, 'en_US', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }
}
