<?php

namespace Magento\Review\Block;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ReinitableConfig;
use Magento\Framework\App\State;
use Magento\TestFramework\ObjectManager;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager;
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getObjectManager();

        parent::setUp();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Review/_files/config.php
     * @dataProvider getCorrectFlagDataProvider
     */
    public function testGetCorrectFlag(
        $path,
        $scope,
        $scopeId,
        $value,
        $expectedResult
    ) {
        /** @var State $appState */
        $appState = $this->objectManager->get(State::class);
        $appState->setAreaCode(Area::AREA_FRONTEND);

        /** @var Value $config */
        $config = $this->objectManager->create(Value::class);
        $config->setPath($path);
        $config->setScope($scope);
        $config->setScopeId($scopeId);
        $config->setValue($value);
        $config->save();
        /** @var ReinitableConfig $reinitableConfig */
        $reinitableConfig = $this->objectManager->create(ReinitableConfig::class);
        $reinitableConfig->reinit();

        /** @var \Magento\Review\Block\Form $form */
        $form = $this->objectManager->create(\Magento\Review\Block\Form::class);
        $result = $form->getAllowWriteReviewFlag();
        $this->assertEquals($result, $expectedResult);
    }

    public function getCorrectFlagDataProvider()
    {
        return [
            [
                'path' => 'catalog/review/allow_guest',
                'scope' => 'websites',
                'scopeId' => '1',
                'value' => 0,
                'expectedResult' => false,
            ],
            [
                'path' => 'catalog/review/allow_guest',
                'scope' => 'websites',
                'scopeId' => '1',
                'value' => 1,
                'expectedResult' => true
            ]
        ];
    }

    private function getObjectManager()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }
}
