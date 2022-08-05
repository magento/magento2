<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testGetHtmlAttributes()
    {
        /** @var File $fileBlock */
        $helper = new ObjectManager($this);
        $collectionFactory = $this->createMock(CollectionFactory::class);

        $fileBlock = $helper->getObject(
            File::class,
            ['factoryCollection' => $collectionFactory]
        );

        $this->assertContains('accept', $fileBlock->getHtmlAttributes());
        $this->assertContains('multiple', $fileBlock->getHtmlAttributes());
    }
}
