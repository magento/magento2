<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Package;

use Magento\Deploy\Package\LocaleResolver;
use Magento\Deploy\Package\Package;
use Magento\Framework\App\Area;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\AppInterface;
use Magento\Framework\Validator\Locale;
use Magento\Store\Model\Config\StoreView;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Deployment Package LocaleResolver class unit tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LocaleResolverTest extends TestCase
{
    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var Package|MockObject
     */
    private $package;

    /**
     * @var StoreView|MockObject
     */
    private $storeView;

    /**
     * @var CollectionFactory|MockObject
     */
    private $userCollectionFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var Locale|MockObject
     */
    private $locale;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var Collection|MockObject
     */
    private $userCollection;

    /**
     * @var UserInterface|MockObject
     */
    private $adminUser;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->package = $this->createMock(Package::class);
        $this->storeView = $this->createMock(StoreView::class);
        $this->adminUser = $this->getMockForAbstractClass(UserInterface::class);
        $this->userCollection = $this->createMock(Collection::class);
        $this->userCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->userCollectionFactory->method('create')->willReturn($this->userCollection);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->locale = $this->createMock(Locale::class);
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['critical'],
            '',
            false
        );
        $this->localeResolver = new LocaleResolver(
            $this->storeView,
            $this->userCollectionFactory,
            $this->deploymentConfig,
            $this->locale,
            $this->logger
        );
    }

    /**
     * Test Get Used Package Locales when there is no DB connection set up yet
     * Should only return en_US by default
     */
    public function testGetUsedPackageLocalesNoDb()
    {
        $this->package->expects(static::exactly(1))->method('getArea')->willReturn(Area::AREA_FRONTEND);
        $this->deploymentConfig->expects(static::exactly(1))->method('get')->willReturn([]);
        $this->storeView->expects(static::exactly(0))->method('retrieveLocales');
        $this->userCollectionFactory->expects(static::exactly(0))->method('create');
        $this->locale->method('isValid')->willReturn(true);

        $locales = $this->localeResolver->getUsedPackageLocales($this->package);
        static::assertEquals(
            [AppInterface::DISTRO_LOCALE_CODE],
            $locales
        );
    }

    /**
     * Test Get Used Package Locales when there is no DB connection set up yet
     * Should only return en_US by default
     */
    public function testGetUsedPackageLocalesNoDbWithDeployment()
    {
        $this->package->expects(static::exactly(1))->method('getArea')->willReturn(Area::AREA_ADMINHTML);
        $this->deploymentConfig->expects(static::exactly(2))->method('get')->willReturn(['zh_SG'], []);
        $this->storeView->expects(static::exactly(0))->method('retrieveLocales');
        $this->userCollectionFactory->expects(static::exactly(0))->method('create');
        $this->locale->method('isValid')->willReturn(true);

        $locales = $this->localeResolver->getUsedPackageLocales($this->package);
        static::assertEquals(
            [AppInterface::DISTRO_LOCALE_CODE, 'zh_SG'],
            $locales
        );
    }

    /**
     * Test Get Used Package Locales when there is no DB connection set up yet
     * Should only return en_US by default
     */
    public function testGetUsedPackageLocalesIllegalLocale()
    {
        $this->package->expects(static::exactly(1))->method('getArea')->willReturn(Area::AREA_ADMINHTML);
        $this->deploymentConfig->expects(static::exactly(2))->method('get')->willReturn(['en_DE'], []);
        $this->storeView->expects(static::exactly(0))->method('retrieveLocales');
        $this->userCollectionFactory->expects(static::exactly(0))->method('create');
        $this->locale->method('isValid')->willReturn(true, false);
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'en_DE argument has invalid value, run info:language:list for list of available locales'
        );
        $this->localeResolver->getUsedPackageLocales($this->package);
    }

    /**
     * Test Get Used Package Locales for a frontend theme
     * Should return used frontend languages
     */
    public function testGetUsedPackageLocalesFrontend()
    {
        $this->package->expects(static::exactly(1))->method('getArea')->willReturn(Area::AREA_FRONTEND);
        $this->deploymentConfig->expects(static::exactly(1))->method('get')->willReturn(['default' => []]);
        $this->storeView->expects(static::exactly(1))->method('retrieveLocales')->willReturn(['de_DE', 'en_GB']);
        $this->userCollectionFactory->expects(static::exactly(0))->method('create');
        $this->locale->method('isValid')->willReturn(true);

        $locales = $this->localeResolver->getUsedPackageLocales($this->package);
        static::assertEquals(
            ['de_DE', 'en_GB'],
            $locales
        );
    }

    /**
     * Test Get Used Package Locales for an admin theme
     * Should return used admin languages, admin deployment configuration languages and en_US by default
     */
    public function testGetUsedPackageLocalesAdmin()
    {
        $this->package->expects(static::exactly(1))->method('getArea')->willReturn(Area::AREA_ADMINHTML);
        $this->deploymentConfig->expects(static::exactly(2))->method('get')->willReturn(['de_AT'], ['default' => []]);
        $this->storeView->expects(static::exactly(0))->method('retrieveLocales');
        $this->userCollectionFactory->expects(static::exactly(1))->method('create');
        $this->locale->method('isValid')->willReturn(true);
        $this->adminUser->expects(static::exactly(2))->method('getInterfaceLocale')->willReturn('nl_NL', 'fr_FR');
        $this->userCollection->method('getIterator')->willReturn(new \ArrayIterator([
            $this->adminUser,
            $this->adminUser,
        ]));

        $locales = $this->localeResolver->getUsedPackageLocales($this->package);
        static::assertEquals(
            [AppInterface::DISTRO_LOCALE_CODE, 'de_AT', 'nl_NL', 'fr_FR'],
            $locales
        );
    }

    /**
     * Test Get Used Package Locales for a theme that is neither frontend nor admin (hypothetical)
     * Should return both used admin and used frontend languages, plus en_US by default
     */
    public function testGetUsedPackageLocalesDefault()
    {
        $this->package->expects(static::exactly(1))->method('getArea')->willReturn(Area::AREA_GLOBAL);
        $this->deploymentConfig->expects(static::exactly(3))->method('get')
            ->willReturn(['de_AT'], ['default' => []], ['default' => []]);
        $this->storeView->expects(static::exactly(1))->method('retrieveLocales')->willReturn(['en_IE', 'fr_LU']);
        $this->userCollectionFactory->expects(static::exactly(1))->method('create');
        $this->locale->method('isValid')->willReturn(true);
        $this->adminUser->expects(static::exactly(2))->method('getInterfaceLocale')->willReturn('nl_NL', 'fr_FR');
        $this->userCollection->method('getIterator')->willReturn(new \ArrayIterator([
            $this->adminUser,
            $this->adminUser,
        ]));

        $locales = $this->localeResolver->getUsedPackageLocales($this->package);
        static::assertEquals(
            [AppInterface::DISTRO_LOCALE_CODE, 'de_AT', 'nl_NL', 'fr_FR', 'en_IE', 'fr_LU'],
            $locales
        );
    }
}
