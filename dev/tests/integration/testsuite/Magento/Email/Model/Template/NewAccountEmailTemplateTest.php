<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Email\Model\Template;

use Magento\Email\Model\ResourceModel\Template\Collection as TemplateCollection;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Phrase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\Bootstrap as TestFrameworkBootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewAccountEmailTemplateTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    protected $storeData = [];

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->storeData['name'] = $this->config->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORES
        );
        $this->storeData['phone'] = $this->config->getValue(
            'general/store_information/phone',
            ScopeInterface::SCOPE_STORES
        );
        $this->storeData['city'] = $this->config->getValue(
            'general/store_information/city',
            ScopeInterface::SCOPE_STORES
        );
        $this->storeData['country'] = $this->config->getValue(
            'general/store_information/country_id',
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @magentoConfigFixture current_store general/store_information/name TestStore
     * @magentoConfigFixture default_store general/store_information/phone 5124666492
     * @magentoConfigFixture default_store general/store_information/hours 10 to 2
     * @magentoConfigFixture default_store general/store_information/street_line1 1 Test Dr
     * @magentoConfigFixture default_store general/store_information/street_line2 2nd Addr Line
     * @magentoConfigFixture default_store general/store_information/city Austin
     * @magentoConfigFixture default_store general/store_information/zip 78739
     * @magentoConfigFixture default_store general/store_information/country_id US
     * @magentoConfigFixture default_store general/store_information/region_id 57
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     */
    public function testNewAccountEmailTemplate(): void
    {

        /** @var MutableScopeConfigInterface $config */
        $config = Bootstrap::getObjectManager()
            ->get(MutableScopeConfigInterface::class);
        $config->setValue(
            'admin/emails/email_template',
            $this->getCustomEmailTemplateId(
                'template_fixture'
            )
        );

        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);
        $userModel->setFirstname(
            'John'
        )->setLastname(
            'Doe'
        )->setUsername(
            'user1'
        )->setPassword(
            TestFrameworkBootstrap::ADMIN_PASSWORD
        )->setEmail(
            'user1@magento.com'
        );
        $userModel->save();

        $userModel->sendNotificationEmailsIfRequired();

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = Bootstrap::getObjectManager()
            ->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        $mailTemplate = $sentMessage->getBody()->bodyToString();

        $storeText = implode(',', $this->storeData);

        $this->assertStringContainsString("John,", $mailTemplate);
        $this->assertStringContainsString("TestStore", $storeText);
        $this->assertStringContainsString("5124666492", $storeText);
        $this->assertStringContainsString("Austin", $storeText);
        $this->assertStringContainsString("US", $storeText);
    }

    /**
     * Return email template id by origin template code
     *
     * @param string $origTemplateCode
     * @return int|null
     * @throws NotFoundException
     */
    private function getCustomEmailTemplateId(string $origTemplateCode): ?int
    {
        $templateId = null;
        $templateCollection = Bootstrap::getObjectManager()
            ->create(TemplateCollection::class);
        foreach ($templateCollection as $template) {
            if ($template->getOrigTemplateCode() == $origTemplateCode) {
                $templateId = (int) $template->getId();
            }
        }
        if ($templateId === null) {
            throw new NotFoundException(new Phrase(
                'Customized %templateCode% email template not found',
                ['templateCode' => $origTemplateCode]
            ));
        }

        return $templateId;
    }
}
