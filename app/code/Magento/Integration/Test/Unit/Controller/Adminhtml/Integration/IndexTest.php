<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTestCase;

class IndexTest extends IntegrationTestCase
{
    public function testIndexAction()
    {
        $this->_verifyLoadAndRenderLayout();
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                Factory::class,
                $this->createMock(Factory::class)
            ],
            [
                SerializerInterface::class,
                $this->createMock(SerializerInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        // renderLayout
        $this->_controller = $this->_createIntegrationController('Index');
        $result = $this->_controller->execute();
        $this->assertNull($result);
    }
}
