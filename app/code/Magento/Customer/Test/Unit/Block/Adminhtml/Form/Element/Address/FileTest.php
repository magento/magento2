<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Form\Element\Address;

use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Form\Element\Address\File;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Test customer address file element block
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $factoryElement = $this->createMock(Factory::class);
        $factoryCollection = $this->createMock(CollectionFactory::class);
        $escaper = $this->createMock(Escaper::class);
        $adminhtmlData = $this->createMock(Data::class);
        $assetRepo = $this->createMock(Repository::class);
        $urlEncoder = $this->createMock(EncoderInterface::class);
        $formKey = $this->createMock(\Magento\Framework\Data\Form\FormKey::class);
        $form = new \Magento\Framework\Data\Form(
            $factoryElement,
            $factoryCollection,
            $formKey
        );
        $class = $this->modelClass();
        $this->model = new $class(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $adminhtmlData,
            $assetRepo,
            $urlEncoder,
        );
        $this->model->setForm($form);
        $adminhtmlData->method('getUrl')
            ->willReturnCallback(
                function (string $path, array $params) {
                    $url = 'http://example.com/admin/' . trim($path, '/');
                    foreach ($params as $key => $value) {
                        $url .= "/$key/$value";
                    }
                    return $url;
                }
            );
        $urlEncoder->method('encode')
            ->willReturnCallback('md5');
    }

    /**
     * Test that the file element html has proper download link
     */
    public function testGetElementHtml(): void
    {
        $expected = 'http://example.com/admin/customer/address/viewfile/file/a7aef9426d9744cdf873c83ee830f6f5';
        $filePath = '/i/m/image.png';
        $this->model->setValue($filePath);
        $this->assertStringContainsString($expected, $this->model->getElementHtml());
    }

    /**
     * @return string
     */
    public function modelClass(): string
    {
        return File::class;
    }
}
