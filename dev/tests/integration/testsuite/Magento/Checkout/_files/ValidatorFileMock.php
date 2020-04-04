<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\_files;

use Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile;

/**
 * Creates mock for ValidatorFile to replace real instance in fixtures.
 */
class ValidatorFileMock extends \PHPUnit\Framework\TestCase
{
    /**
     * Returns mock.
     * @param array|null $fileData
     * @return ValidatorFile|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getInstance($fileData = null)
    {
        if (empty($fileData)) {
            $fileData = [
                'type' => 'image/jpeg',
                'title' => "test.jpg",
                'quote_path' => "custom_options/quote/s/t/4624d2.jpg",
                'order_path' => "custom_options/order/s/t/89d25b4624d2.jpg",
                "fullpath" => "pub/media/custom_options/quote/s/t/e47389d25b4624d2.jpg",
                "size" => "71901",
                "width" => 5,
                "height" => 5,
                "secret_key" => "10839ec1631b77e5e473",
            ];
        }
        $instance = $this->getMockBuilder(ValidatorFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $instance->method('SetProduct')->willReturnSelf();
        $instance->method('validate')->willReturn($fileData);

        return $instance;
    }
}
