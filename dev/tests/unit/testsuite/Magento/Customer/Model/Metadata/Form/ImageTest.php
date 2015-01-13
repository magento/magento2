<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

class ImageTest extends FileTest
{
    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value
     * @param bool $isAjax
     * @return Image
     */
    protected function getClass($value, $isAjax)
    {
        $imageForm = $this->getMock(
            'Magento\Customer\Model\Metadata\Form\Image',
            ['_isUploadedFile'],
            [
                $this->localeMock,
                $this->loggerMock,
                $this->attributeMetadataMock,
                $this->localeResolverMock,
                $value,
                0,
                $isAjax,
                $this->urlEncode,
                $this->fileValidatorMock,
                $this->fileSystemMock,
                $this->uploaderFactoryMock
            ]
        );
        return $imageForm;
    }

    public function validateValueToUploadDataProvider()
    {
        $imagePath = __DIR__ . '/_files/logo.gif';
        return [
            [
                ['"realFileName" is not a valid file.'],
                ['tmp_name' => 'tmp_file', 'name' => 'realFileName'],
                ['valid' => false],
            ],
            [true, ['tmp_name' => $imagePath, 'name' => 'logo.gif']]
        ];
    }
}
