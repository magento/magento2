<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Controller\Adminhtml\Bookmark;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

/**
 * Bookmark Save action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends AbstractAction implements HttpPostActionInterface
{
    /**
     * Identifier for current bookmark
     */
    public const CURRENT_IDENTIFIER = 'current';

    public const ACTIVE_IDENTIFIER = 'activeIndex';

    public const VIEWS_IDENTIFIER = 'views';

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var DecoderInterface
     * @deprecated 101.1.0
     * @see Replaced the usage of Magento\Framework\Json\DecoderInterface by Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonDecoder;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param BookmarkInterfaceFactory $bookmarkFactory
     * @param UserContextInterface $userContext
     * @param DecoderInterface $jsonDecoder
     * @param Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkInterfaceFactory $bookmarkFactory,
        UserContextInterface $userContext,
        DecoderInterface $jsonDecoder,
        ?Json $serializer = null
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
        $this->jsonDecoder = $jsonDecoder;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Action for AJAX request
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \LogicException|LocalizedException
     */
    public function execute()
    {
        if (!$this->userContext->getUserId()) {
            return;
        }

        $bookmark = $this->bookmarkFactory->create();
        $jsonData = $this->_request->getParam('data');
        if (!$jsonData) {
            throw new \InvalidArgumentException('Invalid parameter "data"');
        }
        $data = $this->serializer->unserialize($jsonData);
        $action = key($data);
        switch ($action) {
            case self::ACTIVE_IDENTIFIER:
                $this->updateCurrentBookmark($data[$action]);
                break;

            case self::CURRENT_IDENTIFIER:
                $this->updateBookmark(
                    $bookmark,
                    $action,
                    $bookmark->getTitle(),
                    $jsonData
                );
                $this->updateCurrentBookmarkConfig($data);

                break;

            case self::VIEWS_IDENTIFIER:
                foreach ($data[$action] as $identifier => $data) {
                    $this->updateBookmark(
                        $bookmark,
                        $identifier,
                        $data['label'] ?? '',
                        $jsonData
                    );
                    $this->updateCurrentBookmark($identifier);
                }

                break;

            default:
                throw new \LogicException(__('Unsupported bookmark action.'));
        }
    }

    /**
     * Update bookmarks based on request params
     *
     * @param BookmarkInterface $bookmark
     * @param string $identifier
     * @param string $title
     * @param string $config
     * @return void
     */
    protected function updateBookmark(BookmarkInterface $bookmark, $identifier, $title, $config)
    {
        $updateBookmark = $this->checkBookmark($identifier);
        if ($updateBookmark !== false) {
            $bookmark = $updateBookmark;
        }

        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace($this->_request->getParam('namespace'))
            ->setIdentifier($identifier)
            ->setTitle($title)
            ->setConfig($config);
        $this->bookmarkRepository->save($bookmark);
    }

    /**
     * Update current bookmark
     *
     * @param string $identifier
     * @return void
     * @throws LocalizedException
     */
    protected function updateCurrentBookmark($identifier)
    {
        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->_request->getParam('namespace'));
        $currentConfig = null;
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getIdentifier() == $identifier) {
                $current = $bookmark->getConfig();
                $currentConfig = $current['views'][$bookmark->getIdentifier()]['data'];
                $bookmark->setCurrent(true);
            } else {
                $bookmark->setCurrent(false);
            }
            $this->bookmarkRepository->save($bookmark);
        }

        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getIdentifier() === self::CURRENT_IDENTIFIER && $currentConfig !== null) {
                $bookmarkConfig = $bookmark->getConfig();
                $bookmarkConfig[self::CURRENT_IDENTIFIER] = $currentConfig;
                $bookmark->setConfig($this->serializer->serialize($bookmarkConfig));
                $this->bookmarkRepository->save($bookmark);
                break;
            }
        }
    }

    /**
     * Check bookmark by identifier
     *
     * @param string $identifier
     * @return bool|BookmarkInterface
     */
    protected function checkBookmark($identifier)
    {
        $result = false;

        $updateBookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            $identifier,
            $this->_request->getParam('namespace')
        );

        if ($updateBookmark) {
            $result = $updateBookmark;
        }

        return $result;
    }

    /**
     * Update current bookmark config data
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    private function updateCurrentBookmarkConfig(array $data): void
    {
        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->_request->getParam('namespace'));
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getCurrent()) {
                $bookmarkConfig = $bookmark->getConfig();
                $existingConfig = $bookmarkConfig['views'][$bookmark->getIdentifier()]['data'] ?? null;
                $currentConfig = $data[self::CURRENT_IDENTIFIER] ?? null;
                if ($existingConfig && $currentConfig && $this->isPositionChanged($existingConfig, $currentConfig)) {
                    $bookmarkConfig['views'][$bookmark->getIdentifier()]['data'] = $data[self::CURRENT_IDENTIFIER];
                    $bookmark->setConfig($this->serializer->serialize($bookmarkConfig));
                    $this->bookmarkRepository->save($bookmark);
                }
                break;
            }
        }
    }

    /**
     * Check if the positions for identical filters has changed
     *
     * @param array $existingConfig The existing configuration
     * @param array $currentConfig The current configuration
     * @return bool True if positions have changed, false otherwise
     */
    private function isPositionChanged(array $existingConfig, array $currentConfig): bool
    {
        foreach (['filters', 'positions'] as $key) {
            if (!array_key_exists($key, $existingConfig) || !array_key_exists($key, $currentConfig)) {
                return false;
            }
        }

        return $existingConfig['filters'] === $currentConfig['filters']
            && $existingConfig['positions'] !== $currentConfig['positions'];
    }
}
