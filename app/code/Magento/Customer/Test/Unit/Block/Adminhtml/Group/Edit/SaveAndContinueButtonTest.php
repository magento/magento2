<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Group\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class SaveAndContinueButtonTest
 *
 * Test for class \Magento\Customer\Block\Adminhtml\Group\Edit\SaveAndContinueButton
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveAndContinueButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Group\Edit\SaveAndContinueButton
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Customer\Block\Adminhtml\Group\Edit\SaveAndContinueButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\SaveAndContinueButton::getButtonData
     */
    public function testGetButtonData()
    {
        $data = [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order' => 80,
        ];

        $this->assertEquals($data, $this->model->getButtonData());
    }
}
