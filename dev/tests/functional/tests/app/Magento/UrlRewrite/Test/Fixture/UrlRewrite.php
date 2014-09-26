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

namespace Magento\UrlRewrite\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class UrlRewrite
 */
class UrlRewrite extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\UrlRewrite\Test\Repository\UrlRewrite';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\UrlRewrite\Test\Handler\UrlRewrite\UrlRewriteInterface';

    protected $defaultDataSet = [
        'store_id' => 'Main Website/Main Website Store/Default Store View',
        'request_path' => 'test_request%isolation%',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'virtual',
    ];

    protected $store_id = [
        'attribute_code' => 'store_id',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => 'Default Store View',
        'source' => 'Magento\UrlRewrite\Test\Fixture\UrlRewrite\StoreId',
        'input' => 'select',
    ];

    protected $redirect_type = [
        'attribute_code' => 'redirect_type',
        'backend_type' => 'int',
        'is_required' => '0',
        'input' => 'select',
    ];

    protected $request_path = [
        'attribute_code' => 'request_path',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => 'request_path%isolation%',
        'input' => 'text',
    ];

    protected $target_path = [
        'attribute_code' => 'target_path',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => 'target_path%isolation%',
        'input' => 'text',
    ];

    protected $description = [
        'attribute_code' => 'description',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'input' => 'text',
    ];

    public function getId()
    {
        return $this->getData('id');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function getRedirectType()
    {
        return $this->getData('redirect_type');
    }

    public function getRequestPath()
    {
        return $this->getData('request_path');
    }

    public function getTargetPath()
    {
        return $this->getData('target_path');
    }

    public function getDescription()
    {
        return $this->getData('description');
    }
}
