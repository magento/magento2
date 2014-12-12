<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sitemap\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Sitemap
 *
 */
class Sitemap extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'sitemap_filename' => 'sitemap.xml',
            'sitemap_path' => '/',
        ];
    }
}
