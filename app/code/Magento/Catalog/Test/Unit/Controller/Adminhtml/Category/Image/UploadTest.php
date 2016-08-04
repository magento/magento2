<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Image;

use \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload as Model;
use \Magento\Framework\App\Request\Http as Request;

/**
 * Class UploadTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploadTest extends \PHPUnit_Framework_TestCase
{
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function uploadedImageNameProvider()
    {
        return [
            ['image1', 'image1'],
            ['image2', 'image2'],
            [null, 'image'],
        ];
    }

    /**
     * @param $name
     * @param $savedName
     *
     * @dataProvider uploadedImageNameProvider
     */
    public function testExecuteShouldSaveUploadedImageWithSpecifiedNameToTmpFolder($name, $savedName)
    {
        $request = $this->objectManager->getObject(Request::class);

        $uploader = $this->getMock(
            \Magento\Catalog\Model\ImageUploader::class, ['saveFileToTmpDir'], [], '', false);

        $resultFactory = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class, ['create'], [], '', false);

        $resultFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new \Magento\Framework\DataObject()));

        $model = $this->objectManager->getObject(Model::class, [
            'context' => $this->objectManager->getObject(\Magento\Backend\App\Action\Context::class, [
                'request' => $request,
                'resultFactory' => $resultFactory
            ]),
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