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
namespace Magento\Webapi\Model\Plugin\Service\V1;

use Magento\Integration\Model\Integration;
use Magento\Authorization\Model\Acl\AclRetriever;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * API setup plugin
     *
     * @var \Magento\Webapi\Model\Plugin\Service\V1\Integration
     */
    protected $integrationV1Plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /** @var  AclRetriever */
    protected $aclRetrieverMock;

    /**
     * @var \Magento\Integration\Service\V1\AuthorizationServiceInterface
     */
    protected $integrationAuthServiceMock;

    public function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Integration\Service\V1\Integration', array(), array(), '', false);
        $this->integrationAuthServiceMock = $this->getMockBuilder(
            'Magento\Integration\Service\V1\AuthorizationServiceInterface'
        )->disableOriginalConstructor()->getMock();
        $this->aclRetrieverMock = $this->getMockBuilder('Magento\Authorization\Model\Acl\AclRetriever')
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationV1Plugin = new \Magento\Webapi\Model\Plugin\Service\V1\Integration(
            $this->integrationAuthServiceMock,
            $this->aclRetrieverMock
        );
    }

    public function testAfterDelete()
    {
        $integrationId = 1;
        $integrationsData = array(
            Integration::ID => $integrationId,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::SETUP_TYPE => 1
        );

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('removePermissions')
            ->with($integrationId);
        $this->integrationV1Plugin->afterDelete($this->subjectMock, $integrationsData);
    }
}
