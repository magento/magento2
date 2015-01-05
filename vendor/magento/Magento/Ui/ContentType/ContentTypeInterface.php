<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ContentTypeInterface
 */
interface ContentTypeInterface
{
    /**
     * Render data
     *
     * @param UiComponentInterface $view
     * @param string $template
     * @return mixed
     */
    public function render(UiComponentInterface $view, $template = '');
}
