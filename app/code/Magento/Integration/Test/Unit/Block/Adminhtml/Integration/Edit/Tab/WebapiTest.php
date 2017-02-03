<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Model\Integration as IntegrationModel;

class WebapiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
     */
    private $webapiBlock;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Acl\RootResource
     */
    private $rootResource;

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface
     */
    private $aclResourceProvider;

    /**
     * @var \Magento\Integration\Helper\Data
     */
    private $integrationHelper;

    /**
     * @var \Magento\Integration\Model\IntegrationService
     */
    private $integrationService;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->rootResource = $this->getMockBuilder('Magento\Framework\Acl\RootResource')
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclResourceProvider = $this->getMockBuilder('Magento\Framework\Acl\AclResource\ProviderInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->integrationHelper = $this->getMockBuilder('Magento\Integration\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->integrationService = $this->getMockBuilder('Magento\Integration\Model\IntegrationService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $integrationData
     * @param bool $expectedValue
     * @dataProvider canShowTabProvider
     */
    public function testCanShowTab($integrationData, $expectedValue)
    {
        $this->webapiBlock = $this->getWebapiBlock($integrationData);
        $this->assertEquals($expectedValue, $this->webapiBlock->canShowTab());
    }

    public function canShowTabProvider()
    {
        return [
            'null data' => [
                null,
                true
            ],
            'empty integration data' => [
                [],
                true
            ],
            'manual integration data' => [
                [Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_MANUAL],
                true
            ],
            'config integration data' => [
                [Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_CONFIG],
                false
            ],
        ];
    }

    public function testIsHidden()
    {
        $this->webapiBlock = $this->getWebapiBlock();
        $this->assertFalse($this->webapiBlock->isHidden());
    }

    /**
     * @param string $rootResourceId
     * @param array $integrationData
     * @param array $selectedResources
     * @param bool $expectedValue
     * @dataProvider isEverythingAllowedProvider
     */
    public function testIsEverythingAllowed($rootResourceId, $integrationData, $selectedResources, $expectedValue)
    {
        $this->webapiBlock = $this->getWebapiBlock($integrationData, $selectedResources);
        $this->rootResource->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($rootResourceId));
        $this->assertEquals($expectedValue, $this->webapiBlock->isEverythingAllowed());
    }

    public function isEverythingAllowedProvider()
    {
        return [
            'root resource in array' => [
                2,
                ['integration_id' => 1],
                [1, 2, 3],
                true
            ],
            'root resource not in array' => [
                1,
                ['integration_id' => 1],
                [2, 3, 4],
                false
            ],
            'no integration data' => [
                1,
                [],
                [],
                false
            ],
        ];
    }

    public function testGetTree()
    {
        $this->webapiBlock = $this->getWebapiBlock();
        $resources = [
            1 => [ 'children' => [1, 2, 3] ]
        ];
        $this->aclResourceProvider->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue($resources));
        $rootArray = "rootArrayValue";
        $this->integrationHelper->expects($this->once())
            ->method('mapResources')
            ->with([1, 2, 3])
            ->will($this->returnValue($rootArray));
        $this->assertEquals($rootArray, $this->webapiBlock->getTree());
    }

    /**
     * @param array $integrationData
     * @param array $selectedResources
     * @return \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Webapi
     */
    private function getWebapiBlock($integrationData = [], array $selectedResources = [])
    {
        if ($integrationData) {
            if (isset($integrationData['integration_id'])) {
                $this->integrationService->expects($this->once())
                    ->method('getSelectedResources')
                    ->with($integrationData['integration_id'])
                    ->will($this->returnValue($selectedResources));
            }
        }

        $this->registry->expects($this->any())
            ->method('registry')
            ->with(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION)
            ->will($this->returnValue($integrationData));

        return $this->objectManager->getObject(
            'Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Webapi',
            [
                'registry' => $this->registry,
                'rootResource' => $this->rootResource,
                'aclResourceProvider' => $this->aclResourceProvider,
                'integrationData' => $this->integrationHelper,
                'integrationService' => $this->integrationService,
            ]
        );
    }
}
