<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Widget;

class SkinFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider widgetPlaceholderImagesDataProvider
     */
    public function testWidgetPlaceholderImages($skinImage)
    {
        /** @var \Magento\Framework\View\Asset\Repository $assetRepo */
        $assetRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager()
            ->get(\Magento\Framework\View\Asset\Repository::class);
        $this->assertFileExists(
            $assetRepo->createAsset($skinImage, ['area' => 'adminhtml'])->getSourceFile()
        );
    }

    /**
     * @return array
     */
    public static function widgetPlaceholderImagesDataProvider()
    {
        $result = [];
        /** @var $model \Magento\Widget\Model\Widget */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget::class
        );
        foreach ($model->getWidgetsArray() as $row) {
            /** @var $instance \Magento\Widget\Model\Widget\Instance */
            $instance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Widget\Model\Widget\Instance::class
            );
            $config = $instance->setType($row['type'])->getWidgetConfigAsArray();
            if (isset($config['placeholder_image'])) {
                $result[] = [(string)$config['placeholder_image']];
            }
        }
        return $result;
    }
}
