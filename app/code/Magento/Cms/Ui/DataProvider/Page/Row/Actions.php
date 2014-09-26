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
namespace Magento\Cms\Ui\DataProvider\Page\Row;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\RowInterface;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;

/**
 * Class Actions
 */
class Actions implements RowInterface
{
    /**
     * Url path
     */
    const URL_PATH = 'cms/page/edit';

    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlBuilder $actionUrlBuilder, UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
    }

    /**
     * Get data
     *
     * @param array $dataRow
     * @return mixed
     */
    public function getData(array $dataRow)
    {
        return [
            'edit' => [
                'href' => $this->urlBuilder->getUrl(static::URL_PATH, ['page_id' => $dataRow['page_id']]),
                'title' => __('Edit'),
                'hidden' => true

            ],
            'preview' => [
                'href' => $this->actionUrlBuilder->getUrl(
                    $dataRow['identifier'],
                    isset($dataRow['_first_store_id']) ? $dataRow['_first_store_id'] : null,
                    isset($dataRow['store_code']) ? $dataRow['store_code'] : null
                ),
                'title' => __('Preview')
            ]
        ];
    }
}
