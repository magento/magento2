<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class ElementRenderer
 */
class ElementRenderer implements ElementRendererInterface
{
    /**
     * Ui component
     *
     * @var UiComponentInterface
     */
    protected $element;

    /**
     * Data to render
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param UiComponentInterface $element
     * @param array $data
     */
    public function __construct(UiComponentInterface $element, array $data)
    {
        $this->element = $element;
        $this->data = $data;
    }

    /**
     * Render element
     *
     * @return string
     */
    public function render()
    {
        return $this->element->render($this->data);
    }
}
