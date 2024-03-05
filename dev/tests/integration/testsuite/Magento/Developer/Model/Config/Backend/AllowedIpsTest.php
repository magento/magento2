<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Config\Backend;

class AllowedIpsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $value
     * @param string $expected
     * @magentoDbIsolation enabled
     * @dataProvider fieldDataProvider
     */
    public function testSaveWithEscapeHtml($value, $expected)
    {
        /**
         * @var \Magento\Developer\Model\Config\Backend\AllowedIps
         */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Developer\Model\Config\Backend\AllowedIps::class
        );
        $model->setValue($value);
        $model->setPath('path');
        $model->beforeSave();
        $model->save();
        $this->assertEquals($expected, $model->getValue());
    }

    /**
     * @return array
     */
    public static function fieldDataProvider()
    {
        return [
            ['<'.'script>alert(\'XSS\')</script>', '' ],
            ['10.64.202.22, <'.'script>alert(\'XSS\')</script>', '10.64.202.22' ]
        ];
    }
}
