<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Form;

use Magento\Customer\Model\Session;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\ButtonLockManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class EditTest extends TestCase
{
    private const SAVE_BUTTON_XPATH = '//*[@id="form-validate"]//*[@type="submit"]';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Edit
     */
    private $block;

    /** @var Session */
    private $customerSession;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->block = $this->layout->createBlock(Edit::class);
        $this->block->setTemplate('Magento_Customer::form/edit.phtml');
    }

    /**
     * @return void
     */
    public function testCustomerEditButton(): void
    {
        $code = 'customer_edit';
        $buttonLock = $this->getMockBuilder(\Magento\ReCaptchaUi\Model\ButtonLock::class)
            ->disableOriginalConstructor()
            ->disableAutoload()
            ->setMethods(['isDisabled', 'getCode'])
            ->getMock();
        $buttonLock->expects($this->atLeastOnce())->method('getCode')->willReturn($code);
        $buttonLock->expects($this->atLeastOnce())->method('isDisabled')->willReturn(false);
        $buttonLockManager = $this->objectManager->create(
            ButtonLockManager::class,
            ['buttonLockPool' => ['customer_edit_form_submit' => $buttonLock]]
        );
        $this->block->setButtonLockManager($buttonLockManager);

        $this->customerSession->loginById(1);
        $result = $this->block->toHtml();
        $this->assertFalse($buttonLockManager->isDisabled($code));
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::SAVE_BUTTON_XPATH, $result),
            'Customer Edit Button wasn\'t found in the page'
        );
    }
}
