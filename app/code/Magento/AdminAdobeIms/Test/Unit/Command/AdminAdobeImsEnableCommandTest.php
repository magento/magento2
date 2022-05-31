<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Command;

use Exception;
use Magento\AdminAdobeIms\Console\Command\AdminAdobeImsEnableCommand;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\UpdateTokensService;
use Magento\AdminAdobeIms\Service\ImsCommandOptionService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminAdobeImsEnableCommandTest extends TestCase
{
    /**
     * @var ImsConfig
     */
    private $adminImsConfigMock;

    /**
     * @var ImsConnection
     */
    private $adminImsConnectionMock;

    /**
     * @var ImsCommandOptionService
     */
    private $imsCommandOptionService;

    /**
     * @var TypeListInterface
     */
    private $typeListInterface;

    /**
     * @var UpdateTokensService
     */
    private $updateTokensService;

    /**
     * @var QuestionHelper
     */
    private $questionHelperMock;

    /**
     * @var AdminAdobeImsEnableCommand
     */
    private $enableCommand;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->adminImsConnectionMock = $this->createMock(ImsConnection::class);
        $this->imsCommandOptionService = $this->createMock(ImsCommandOptionService::class);
        $this->typeListInterface = $this->createMock(TypeListInterface::class);
        $this->updateTokensService = $this->createMock(UpdateTokensService::class);

        $this->questionHelperMock = $this->getMockBuilder(QuestionHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->enableCommand = $objectManagerHelper->getObject(
            AdminAdobeImsEnableCommand::class,
            [
                'adminImsConfig' => $this->adminImsConfigMock,
                'adminImsConnection' => $this->adminImsConnectionMock,
                'imsCommandOptionService' => $this->imsCommandOptionService,
                'cacheTypeList' => $this->typeListInterface,
                'updateTokenService' => $this->updateTokensService
            ]
        );
    }

    /**
     * Test AdminAdobeIms Command calls cache clear and return correct message
     *
     * @param bool $testAuthMode
     * @param InvokedCountMatcher$enableMethodCallExpection
     * @param InvokedCountMatcher $cleanMethodCallExpection
     * @param string $outputMessage
     * @param bool $isTwoFactorAuthEnabled
     * @return void
     * @throws Exception
     * @dataProvider cliCommandProvider
     */
    public function testAdminAdobeImsModuleEnableWillClearCacheWhenSuccessful(
        bool $testAuthMode,
        InvokedCountMatcher $enableMethodCallExpection,
        InvokedCountMatcher $cleanMethodCallExpection,
        string $outputMessage,
        bool $isTwoFactorAuthEnabled
    ): void {
        $inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();

        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $this->questionHelperMock->method('ask')->willReturn('ORGId');

        $this->imsCommandOptionService->method('getOrganizationId')->willReturn('orgId');
        $this->imsCommandOptionService->method('getClientId')->willReturn('clientId');
        $this->imsCommandOptionService->method('getClientSecret')->willReturn('clientSecret');
        $this->imsCommandOptionService->method('isTwoFactorAuthEnabled')->willReturn($isTwoFactorAuthEnabled);

        $this->adminImsConnectionMock->method('testAuth')
            ->willReturn($testAuthMode);

        $this->adminImsConfigMock
            ->expects($enableMethodCallExpection)
            ->method('enableModule');

        $this->typeListInterface
            ->expects($cleanMethodCallExpection)
            ->method('cleanType')
            ->with(Config::TYPE_IDENTIFIER);

        $this->updateTokensService
            ->expects($cleanMethodCallExpection)
            ->method('execute');

        $outputMock->expects($this->once())
            ->method('writeln')
            ->with($outputMessage, null)
            ->willReturnSelf();

        $this->enableCommand->setHelperSet($this->getHelperSet());
        $this->enableCommand->run($inputMock, $outputMock);
    }

    /**
     * DataProvider for CLI Command
     *
     * @return array[]
     */
    public function cliCommandProvider(): array
    {
        return [
            [
                true,
                $this->once(),
                $this->once(),
                'Admin Adobe IMS integration is enabled',
                true
            ],
            [
                false,
                $this->never(),
                $this->never(),
                '<error>The Client ID, Client Secret, Organization ID and 2FA are required ' .
                'when enabling the Admin Adobe IMS Module</error>',
                true
            ],
            [
                true,
                $this->never(),
                $this->never(),
                '<error>The Client ID, Client Secret, Organization ID and 2FA are required ' .
                'when enabling the Admin Adobe IMS Module</error>',
                false
            ],
            [
                false,
                $this->never(),
                $this->never(),
                '<error>The Client ID, Client Secret, Organization ID and 2FA are required ' .
                'when enabling the Admin Adobe IMS Module</error>',
                false
            ]
        ];
    }

    /**
     * Create a new HelperSet
     *
     * @return HelperSet
     */
    private function getHelperSet(): HelperSet
    {
        return new HelperSet([
            new FormatterHelper(),
            new DebugFormatterHelper(),
            new ProcessHelper(),
            'question' => $this->questionHelperMock,
        ]);
    }
}
