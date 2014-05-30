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

namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

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
        $this->directory = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
        $this->directory->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));
        $filesystem = $this->getMock('\Magento\Framework\App\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem::ROOT_DIR)
            ->will($this->returnValue($this->directory));
        $this->rule = $this->getMock(
            '\Magento\Framework\View\Design\Fallback\Rule\RuleInterface', array(), array(), '', false
        );
        $rulePool = $this->getMock('Magento\Framework\View\Design\Fallback\RulePool', array(), array(), '', false);
        $rulePool->expects($this->any())
            ->method('getRule')
            ->with('type')
            ->will($this->returnValue($this->rule));
        $cache = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\FileResolution\Fallback\CacheDataInterface'
        );
        $cache->expects($this->any())
            ->method('getFromCache')
            ->will($this->returnValue(false));
        $this->object = new Alternative($filesystem, $rulePool, $cache, ['css' => ['less']]);
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

        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $rulePool = $this->getMock('Magento\Framework\View\Design\Fallback\RulePool', array(), array(), '', false);
        $cache = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\FileResolution\Fallback\CacheDataInterface'
        );
        new Alternative($filesystem, $rulePool, $cache, $alternativeExtensions);
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

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())
            ->method('getFullPath')
            ->will($this->returnValue('magento_theme'));
        $this->rule->expects($this->atLeastOnce())
            ->method('getPatternDirs')
            ->will($this->returnValue(['some/dir']));

        $fileExistsMap = [
            ['some/dir/file.css', false],
            ['some/dir/file.less', true],
        ];
        $this->directory->expects($this->any())
            ->method('isExist')
            ->will($this->returnValueMap($fileExistsMap));

        $actual = $this->object->resolve('type', $requestedFile, 'frontend', $theme, 'en_US', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }
}
