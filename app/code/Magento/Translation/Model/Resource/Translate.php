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
namespace Magento\Translation\Model\Resource;

class Translate extends \Magento\Framework\Model\Resource\Db\AbstractDb implements
    \Magento\Framework\Translate\ResourceInterface
{

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var null|string
     */
    protected $scope;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param null|string $scope
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        $scope = null
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->scope = $scope;
        parent::__construct($resource);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('translation', 'key_id');
    }

    /**
     * Retrieve translation array for store / locale code
     *
     * @param int $storeId
     * @param string $locale
     * @return array
     */
    public function getTranslationArray($storeId = null, $locale = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->getStoreId();
        }

        $adapter = $this->_getReadAdapter();
        if (!$adapter) {
            return array();
        }

        $select = $adapter->select()
            ->from($this->getMainTable(), array('string', 'translate'))
            ->where('store_id IN (0 , :store_id)')
            ->where('locale = :locale')
            ->order('store_id');

        $bind = array(':locale' => (string)$locale, ':store_id' => $storeId);

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Retrieve translations array by strings
     *
     * @param array $strings
     * @param int|null $storeId
     * @return array
     */
    public function getTranslationArrayByStrings(array $strings, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->getStoreId();
        }

        $adapter = $this->_getReadAdapter();
        if (!$adapter) {
            return array();
        }

        if (empty($strings)) {
            return array();
        }

        $bind = array(':store_id' => $storeId);
        $select = $adapter->select()
            ->from($this->getMainTable(), array('string', 'translate'))
            ->where('string IN (?)', $strings)
            ->where('store_id = :store_id');

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Retrieve table checksum
     *
     * @return int
     */
    public function getMainChecksum()
    {
        return $this->getChecksum($this->getMainTable());
    }

    /**
     * Retrieve current store identifier
     *
     * @return int
     */
    protected function getStoreId()
    {
        return $this->scopeResolver->getScope($this->scope)->getId();
    }
}
