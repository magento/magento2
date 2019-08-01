<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Framework\Data\FormFactory;
use Magento\Framework\Data\Form\Element\TimeFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CollectionTimeLabel block.
 */
class CollectionTimeLabelTest extends TestCase
{
    /**
     * Test render will add comment considering config locale(default en_US).
     *
     * @magentoAppIsolation enabled
     */
    public function testRenderWithDefaultLocale()
    {
        $result = $this->render();
        $this->assertRegExp('/<span>Pacific Standard Time/', $result);
    }

    /**
     * Test render will add comment considering config locale(non-default de_DE).
     *
     * @magentoConfigFixture default_store general/locale/code de_DE
     * @magentoAppIsolation enabled
     */
    public function testRenderWithNonDefaultLocale()
    {
        $result = $this->render();
        $this->assertRegExp('/<span>Nordamerikanische Westküsten-Normalzeit/', $result);
    }

    /**
     * Render 'time' element.
     *
     * @return string
     */
    private function render()
    {
        $collectionTimeLabel = Bootstrap::getObjectManager()->get(CollectionTimeLabelFactory::class)->create();
        $form = Bootstrap::getObjectManager()->get(FormFactory::class)->create();
        $element = Bootstrap::getObjectManager()->get(TimeFactory::class)->create();
        $element->setForm($form);

        return $collectionTimeLabel->render($element);
    }
}
