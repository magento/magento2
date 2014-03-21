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
            array('_isUploadedFile'),
            array(
                $this->localeMock,
                $this->loggerMock,
                $this->attributeMetadataMock,
                $this->localeResolverMock,
                $value,
                0,
                $isAjax,
                $this->coreDataMock,
                $this->fileValidatorMock,
                $this->fileSystemMock
            )
        );
        return $imageForm;
    }

    public function validateValueToUploadDataProvider()
    {
        $imagePath = __DIR__ . '/_files/logo.gif';
        return array(
            array(
                array('"realFileName" is not a valid file.'),
                array('tmp_name' => 'tmp_file', 'name' => 'realFileName'),
                array('valid' => false)
            ),
            array(true, array('tmp_name' => $imagePath, 'name' => 'logo.gif'))
        );
    }
}
