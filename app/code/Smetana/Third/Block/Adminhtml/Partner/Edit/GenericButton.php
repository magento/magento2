<?php
namespace Smetana\Third\Block\Adminhtml\Partner\Edit;

use Magento\Backend\Block\Widget\Context;

/**
 * Abstract Class GenericButton
 *
 * @package Smetana\Third\Block\Adminhtml\Partner\Edit
 */
abstract class GenericButton
{
    /**
     * Context instance
     *
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Get Partner id
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->context->getRequest()
            ->getParam('id');
    }

    /**
     * Get url
     *
     * @param string $path
     * @param array $params
     *
     * @return string
     */
    public function getPath(string $path, array $params): string
    {
        return $this->context->getUrlBuilder()->getUrl($path, $params);
    }
}
