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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Page\Asset;

class MinifiedTest extends \PHPUnit_Framework_TestCase
{
    const ORIG_SOURCE_FILE = 'original.js';
    const MINIFIED_SOURCE_FILE = 'original.min.js';
    const MINIFIED_URL = 'http://localhost/original.min.js';
    const ORIGINAL_URL = 'http://localhost/original.js';

    /**
     * @var \Magento\Core\Model\Page\Asset\LocalInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_asset;

    /**
     * @var \Magento\Code\Minifier|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_minifier;

    /**
     * @var \Magento\Core\Model\View\Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewUrl;

    /**
     * @var \Magento\Core\Model\Logger|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \Magento\Core\Model\Page\Asset\Minified
     */
    protected $_model;

    protected function setUp()
    {
        $this->_asset = $this->getMockForAbstractClass(
            'Magento\Core\Model\Page\Asset\LocalInterface',
            array(),
            '',
            false
        );
        $this->_minifier = $this->getMock('Magento\Code\Minifier', array('getMinifiedFile'), array(), '', false);
        $this->_viewUrl = $this->getMock('Magento\Core\Model\View\Url', array(), array(), '', false);
        $this->_logger = $this->getMock('Magento\Core\Model\Logger', array(), array(), '', false);

        $this->_model = new \Magento\Core\Model\Page\Asset\Minified($this->_asset, $this->_minifier, $this->_viewUrl,
            $this->_logger
        );
    }

    protected function tearDown()
    {
        $this->_asset = null;
        $this->_minifier = null;
        $this->_viewUrl = null;
        $this->_logger = null;
        $this->_model = null;
    }

    public function testGetUrl()
    {
        $this->_prepareProcessMock();
        $this->assertSame(self::MINIFIED_URL, $this->_model->getUrl());
        $this->assertSame(self::MINIFIED_URL, $this->_model->getUrl());
    }

    public function testGetSourceFile()
    {
        $this->_prepareProcessMock();
        $this->assertSame(self::MINIFIED_SOURCE_FILE, $this->_model->getSourceFile());
        $this->assertSame(self::MINIFIED_SOURCE_FILE, $this->_model->getSourceFile());
    }

    protected function _prepareProcessMock()
    {
        $this->_asset->expects($this->once())
            ->method('getSourceFile')
            ->will($this->returnValue(self::ORIG_SOURCE_FILE));
        $this->_minifier->expects($this->once())
            ->method('getMinifiedFile')
            ->with(self::ORIG_SOURCE_FILE)
            ->will($this->returnValue(self::MINIFIED_SOURCE_FILE));
        $this->_viewUrl->expects($this->any())
            ->method('getPublicFileUrl')
            ->with(self::MINIFIED_SOURCE_FILE)
            ->will($this->returnValue(self::MINIFIED_URL));
    }

    public function testProcessException()
    {
        $this->_asset->expects($this->once())
            ->method('getSourceFile')
            ->will($this->returnValue(self::ORIG_SOURCE_FILE));
        $this->_asset->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue(self::ORIGINAL_URL));

        $this->_minifier->expects($this->once())
            ->method('getMinifiedFile')
            ->with(self::ORIG_SOURCE_FILE)
            ->will($this->throwException(new \Exception('Error')));

        $this->_viewUrl->expects($this->never())
            ->method('getPublicFileUrl');

        $this->assertSame(self::ORIGINAL_URL, $this->_model->getUrl());
        $this->assertSame(self::ORIG_SOURCE_FILE, $this->_model->getSourceFile());
    }

    public function testGetContent()
    {
        $contentType = 'content_type';
        $this->_asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue($contentType));
        $this->assertSame($contentType, $this->_model->getContentType());
    }
}
