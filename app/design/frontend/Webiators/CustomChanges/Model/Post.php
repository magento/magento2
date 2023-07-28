<?php
namespace Webiators\CustomChanges\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'webiators_myfirstmodule_post';

	protected $_cacheTag = 'webiators_myfirstmodule_post';

	protected $_eventPrefix = 'webiators_myfirstmodule_post';

	protected function _construct()
	{
		$this->_init('Webiators\CustomChanges\Model\ResourceModel\Post');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
