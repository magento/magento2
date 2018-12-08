<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Bookmark;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

/**
 * Class Save action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends AbstractAction implements HttpPostActionInterface
{
    /**
     * Identifier for current bookmark
     */
    const CURRENT_IDENTIFIER = 'current';

    const ACTIVE_IDENTIFIER = 'activeIndex';

    const VIEWS_IDENTIFIER = 'views';

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
     * @deprecated
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
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
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
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
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
        $this->jsonDecoder = $jsonDecoder;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Action for AJAX request
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute()
    {
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

                break;

            case self::VIEWS_IDENTIFIER:
                foreach ($data[$action] as $identifier => $data) {
                    $this->updateBookmark(
                        $bookmark,
                        $identifier,
                        isset($data['label']) ? $data['label'] : '',
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
     */
    protected function updateCurrentBookmark($identifier)
    {
        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->_request->getParam('namespace'));
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getIdentifier() == $identifier) {
                $bookmark->setCurrent(true);
            } else {
                $bookmark->setCurrent(false);
            }
            $this->bookmarkRepository->save($bookmark);
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
}
