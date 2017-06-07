<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Image;

use Magento\Catalog\Controller\Adminhtml\Category\Image\Upload as Model;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class UploadTest @covers \Magento\Catalog\Model\ImageUploader.
 */
class UploadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * ObjectManager instance holder.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Prepare subject for tests.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Test Uploader::execute() handle request and move image into tmp dir.
     *
     * @param string $name
     * @param string $savedName
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($name, $savedName)
    {
        $cookieName = 'testName';
        $sessionId = 'testSessionId';
        $lifetime = 'testLifetime';
        $path = 'testPath';
        $domain = 'testDomain';
        $data = [
            'cookie' => [
                'name' => $cookieName,
                'value' => $sessionId,
                'lifetime' => $lifetime,
                'path' => $path,
                'domain' => $domain
            ]
        ];
        $request = $this->objectManager->getObject(Request::class);
        $uploader = $this->getMockBuilder(ImageUploader::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveFileToTmpDir'])
            ->getMock();
        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new DataObject()));
        $session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session->expects($this->once())
            ->method('getName')
            ->willReturn($cookieName);
        $session->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);
        $session->expects($this->once())
            ->method('getCookieLifeTime')
            ->willReturn($lifetime);
        $session->expects($this->once())
            ->method('getCookiePath')
            ->willReturn($path);
        $session->expects($this->once())
            ->method('getCookieDomain')
            ->willReturn($domain);
        $model = $this->objectManager->getObject(Model::class, [
            'request' => $request,
            'resultFactory' => $resultFactory,
            'imageUploader' => $uploader,
            '_session' => $session
        ]);
        $uploader->expects($this->once())
            ->method('saveFileToTmpDir')
            ->with($savedName)
            ->will($this->returnValue([]));
        $request->setParam('param_name', $name);
        $result = $model->execute();
        $this->assertSame($data, $result->getData());
    }

    /**
     * Data for testExecute.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['image1', 'image1'],
            ['image2', 'image2'],
            [null, 'image'],
        ];
    }
}
