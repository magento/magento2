<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Image;

use Magento\Catalog\Controller\Adminhtml\Category\Image\Upload as Model;
use Magento\Framework\App\Request\Http as Request;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;

/**
 * Class UploadTest
 */
class UploadTest extends \PHPUnit_Framework_TestCase
{
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function executeDataProvider()
    {
        return [
            ['image1', 'image1'],
            ['image2', 'image2'],
            [null, 'image'],
        ];
    }

    /**
     * @param string $name
     * @param string $savedName
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($name, $savedName)
    {
        $request = $this->objectManager->getObject(Request::class);

        $uploader = $this->getMock(ImageUploader::class, ['saveFileToTmpDir'], [], '', false);

        $resultFactory = $this->getMock(ResultFactory::class, ['create'], [], '', false);

        $resultFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new DataObject()));

        $model = $this->objectManager->getObject(Model::class, [
            'request' => $request,
            'resultFactory' => $resultFactory,
            'imageUploader' => $uploader
        ]);

        $uploader->expects($this->once())
            ->method('saveFileToTmpDir')
            ->with($savedName)
            ->will($this->returnValue([]));

        $request->setParam('param_name', $name);

        $model->execute();
    }
}
