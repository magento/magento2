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
namespace Magento\Framework\Css\PreProcessor;

class LessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Less\FileGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileGenerator;

    /**
     * @var \Magento\Framework\Css\PreProcessor\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Chain
     */
    private $chain;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Less
     */
    private $object;

    protected function setUp()
    {
        $this->fileGenerator = $this->getMock('\Magento\Framework\Less\FileGenerator', array(), array(), '', false);
        $this->adapter = $this->getMockForAbstractClass('\Magento\Framework\Css\PreProcessor\AdapterInterface');
        $asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())->method('getContentType')->will($this->returnValue('origType'));
        $this->chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($asset, 'original content', 'origType');
        $this->object = new \Magento\Framework\Css\PreProcessor\Less($this->fileGenerator, $this->adapter);
    }

    public function testProcess()
    {
        $expectedContent = 'updated content';
        $tmpFile = 'tmp/file.ext';
        $this->fileGenerator->expects($this->once())
            ->method('generateLessFileTree')
            ->with($this->chain)
            ->will($this->returnValue($tmpFile));
        $this->adapter->expects($this->once())
            ->method('process')
            ->with($tmpFile)
            ->will($this->returnValue($expectedContent));
        $this->object->process($this->chain);
        $this->assertEquals($expectedContent, $this->chain->getContent());
        $this->assertEquals('css', $this->chain->getContentType());
    }
}
