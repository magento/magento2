<?php
declare(strict_types=1);

namespace Chechur\Blog\Block\Adminhtml\Post\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var BlockRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param Context $context
     * @param BlockRepositoryInterface $postRepository
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Context $context,
        BlockRepositoryInterface $postRepository,
        $authorization = null
    )
    {
        $this->context = $context;
        $this->postRepository = $postRepository;
        $this->authorization = $authorization
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\AuthorizationInterface::class
            );
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->context->getRequest()->getParam('id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
