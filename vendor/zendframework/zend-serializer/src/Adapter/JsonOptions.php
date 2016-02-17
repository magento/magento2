<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

use Zend\Json\Json as ZendJson;
use Zend\Serializer\Exception;

class JsonOptions extends AdapterOptions
{
    /**
     * @var int
     */
    protected $cycleCheck = false;

    protected $enableJsonExprFinder = false;

    protected $objectDecodeType = ZendJson::TYPE_ARRAY;

    /**
     * @param  bool $flag
     * @return JsonOptions
     */
    public function setCycleCheck($flag)
    {
        $this->cycleCheck = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getCycleCheck()
    {
        return $this->cycleCheck;
    }

    /**
     * @param  bool $flag
     * @return JsonOptions
     */
    public function setEnableJsonExprFinder($flag)
    {
        $this->enableJsonExprFinder = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableJsonExprFinder()
    {
        return $this->enableJsonExprFinder;
    }

    /**
     * @param  int $type
     * @return JsonOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setObjectDecodeType($type)
    {
        if ($type != ZendJson::TYPE_ARRAY && $type != ZendJson::TYPE_OBJECT) {
            throw new Exception\InvalidArgumentException(
                'Unknown decode type: ' . $type
            );
        }

        $this->objectDecodeType = (int) $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectDecodeType()
    {
        return $this->objectDecodeType;
    }
}
