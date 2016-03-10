<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class FetchRatesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test fetch action without service
     */
    public function testFetchRatesActionWithoutService()
    {
        $request = $this->getRequest();
        $request->setParam(
            'rate_services',
            null
        );
        $this->dispatch('backend/admin/system_currency/fetchRates');

        $this->assertSessionMessages(
            $this->contains('Please specify a correct Import Service.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test save action with nonexistent service
     */
    public function testFetchRatesActionWithNonexistentService()
    {
        $request = $this->getRequest();
        $request->setParam(
            'rate_services',
            'non-existent-service'
        );
        $this->dispatch('backend/admin/system_currency/fetchRates');

        $this->assertSessionMessages(
            $this->contains('We can\'t initialize the import model.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test save action with nonexistent service
     */
    public function testFetchRatesActionWithServiceErrors()
    {
        $this->runActionWithMockedImportService(['We can\'t retrieve a rate from url']);

        $this->assertSessionMessages(
            $this->contains('Click "Save" to apply the rates we found.'),
            \Magento\Framework\Message\MessageInterface::TYPE_WARNING
        );
    }

    /**
     * Test save action with nonexistent service
     */
    public function testFetchRatesActionWithoutServiceErrors()
    {
        $this->runActionWithMockedImportService();

        $this->assertSessionMessages(
            $this->contains('Click "Save" to apply the rates we found.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Run fetchRates with mocked external import service
     *
     * @param array $messages messages from external import service
     */
    protected function runActionWithMockedImportService(array $messages = [])
    {
        $importServiceMock = $this->getMockBuilder('\Magento\Directory\Model\Currency\Import\Webservicex')
            ->disableOriginalConstructor()
            ->getMock();

        $importServiceMock->method('fetchRates')
            ->willReturn(['USD' => ['USD' => 1]]);

        $importServiceMock->method('getMessages')
            ->willReturn($messages);

        $backendSessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $importServiceFactoryMock = $this->getMockBuilder('\Magento\Directory\Model\Currency\Import\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $importServiceFactoryMock->method('create')
            ->willReturn($importServiceMock);

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMap = [
            ['Magento\Directory\Model\Currency\Import\Factory', $importServiceFactoryMock],
            ['Magento\Backend\Model\Session', $backendSessionMock]
        ];

        $objectManagerMock->method('get')
            ->will($this->returnValueMap($objectManagerMap));

        $context =  $this->_objectManager->create(
            'Magento\Backend\App\Action\Context',
            ["objectManager" => $objectManagerMock]
        );
        $registry =  $this->_objectManager->get('Magento\Framework\Registry');

        $this->getRequest()->setParam('rate_services', 'webservicex');

        $action = new \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates(
            $context,
            $registry
        );
        $action->execute();
    }
}
