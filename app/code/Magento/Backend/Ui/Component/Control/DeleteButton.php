<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Represents delete button with pre-configured options
 * Provide an ability to show confirmation message on click on the "Delete" button
 *
 * @api
 * @since 101.0.0
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var string
     */
    private $confirmationMessage;

    /**
     * @var string
     */
    private $idFieldName;

    /**
     * @var string
     */
    private $deleteRoutePath;

    /**
     * @var int
     */
    private $sortOrder;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param string $confirmationMessage
     * @param string $idFieldName
     * @param string $deleteRoutePath
     * @param int $sortOrder
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        string $confirmationMessage,
        string $idFieldName,
        string $deleteRoutePath,
        int $sortOrder
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->confirmationMessage = $confirmationMessage;
        $this->idFieldName = $idFieldName;
        $this->deleteRoutePath = $deleteRoutePath;
        $this->sortOrder = $sortOrder;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getButtonData()
    {
        $data = [];
        $fieldId = $this->escaper->escapeJs($this->escaper->escapeHtml($this->request->getParam($this->idFieldName)));
        if (null !== $fieldId) {
            $url = $this->urlBuilder->getUrl($this->deleteRoutePath);
            $escapedMessage = $this->escaper->escapeJs($this->escaper->escapeHtml($this->confirmationMessage));
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => "deleteConfirm('{$escapedMessage}', '{$url}', {data:{{$this->idFieldName}:{$fieldId}}})",
                'sort_order' => $this->sortOrder,
            ];
        }
        return $data;
    }
}
