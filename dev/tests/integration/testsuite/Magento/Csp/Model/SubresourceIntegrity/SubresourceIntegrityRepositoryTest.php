<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;

/**
 * Integration test to cover end to end SRI Generation
 */
class SubresourceIntegrityRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem|null
     */
    private ?Filesystem $filesystem = null;

    /**
     * @var WriteInterface|null
     */
    private ?WriteInterface $staticDir = null;

    /**
     * @var DeployStaticContent|null
     */
    private ?DeployStaticContent $deployService = null;

    /**
     * @var HashGenerator|null
     */
    private ?HashGenerator $hashGenerator = null;

    /**
     * @var array
     */
    private $options = [
        Options::NO_JAVASCRIPT => false,
        Options::NO_JS_BUNDLE => false,
        Options::NO_CSS => false,
        Options::NO_IMAGES => false,
        Options::NO_FONTS => false,
        Options::NO_HTML => false,
        Options::NO_MISC => false,
        Options::NO_HTML_MINIFY => false,
        Options::AREA => ['frontend'],
        Options::EXCLUDE_AREA => ['none'],
        Options::THEME => ['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3'],
        Options::EXCLUDE_THEME => ['Magento/backend', 'Magento/luma'],
        Options::LANGUAGE => ['en_US'],
        Options::EXCLUDE_LANGUAGE => ['none'],
        Options::JOBS_AMOUNT => 0,
        Options::SYMLINK_LOCALE => false,
        Options::NO_PARENT => false,
        Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_QUICK,
    ];

    /**
     * @var SubresourceIntegrityRepository|null
     */
    private ?SubresourceIntegrityRepository $integrityRepository = null;

    /**
     * @var SubresourceIntegrityRepositoryPool|null
     */
    private ?SubresourceIntegrityRepositoryPool $integrityRepositoryPool = null;

    /**
     * Initialize Dependencies
     *
     * @return void
     * @throws FileSystemException
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->integrityRepository = $objectManager->get(SubresourceIntegrityRepository::class);
        $this->integrityRepositoryPool = $objectManager->get(SubresourceIntegrityRepositoryPool::class);
        $this->hashGenerator = $objectManager->get(HashGenerator::class);

        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $this->deployService = $objectManager->create(
            DeployStaticContent::class,
            ['logger' => $logger]
        );
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    /**
     * Integration test to check caches save hash value after static content deploy
     *
     * @magentoDataFixture Magento/Deploy/_files/theme.php
     * @covers \Magento\Csp\Model\SubresourceIntegrityRepositoryPool::get
     * @covers \Magento\Csp\Model\SubresourceIntegrityRepository::getAll
     * @covers \Magento\Csp\Model\SubresourceIntegrityRepository::getByPath
     * @covers \Magento\Csp\Model\SubresourceIntegrity\HashGenerator::generate
     * @return void
     * @throws LocalizedException
     */
    public function testDeploy(): void
    {
        $this->assertEmpty($this->integrityRepository->getAll());
        $this->deployService->deploy($this->options);
        $repository = $this->integrityRepositoryPool->get('frontend');
        $this->assertNotEmpty($repository->getAll());
        $integrity = $repository->getByPath('frontend/Magento/zoom3/en_US/js/file3.js');
        $filePath = $this->staticDir->getAbsolutePath('frontend/Magento/zoom3/en_US/js/file3.js');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $fileContents = file_get_contents($filePath);
        $hash = $this->hashGenerator->generate($fileContents);
        $this->assertEquals($hash, $integrity->getHash());
    }
}
