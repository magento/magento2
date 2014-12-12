<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\View\Design\Fallback\RulePool;

class LocaleFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    /**
     * @var LocaleFile
     */
    protected $object;

    protected function setUp()
    {
        $this->resolver = $this->getMock('Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface');
        $this->object = new LocaleFile($this->resolver);
    }

    public function testGetFile()
    {
        $theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');
        $expected = 'some/file.ext';
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_LOCALE_FILE, 'file.ext', 'frontend', $theme, 'en_US', null)
            ->will($this->returnValue($expected));
        $actual = $this->object->getFile('frontend', $theme, 'en_US', 'file.ext');
        $this->assertSame($expected, $actual);
    }
}
