<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Image;

use Magento\CardinalCommerce\Model\Response\JwtPayloadValidator;
use Magento\Catalog\Controller\Adminhtml\Category\Image\Upload as Model;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return array
     */
    public static function executeDataProvider()
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

        $uploader = $this->createPartialMock(ImageUploader::class, ['saveFileToTmpDir']);

        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);

        $resultFactory->expects($this->once())
            ->method('create')
            ->willReturn(new DataObject());

        $model = $this->objectManager->getObject(Model::class, [
            'request' => $request,
            'resultFactory' => $resultFactory,
            'imageUploader' => $uploader
        ]);

        $uploader->expects($this->once())
            ->method('saveFileToTmpDir')
            ->with($savedName)
            ->willReturn([]);

        $request->setParam('param_name', $name);

        $model->execute();
    }
}
