<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\View\Model;

/**
 * Interface describing a Retrievable Child Model
 *
 * Models implementing this interface provide a way to get there children by capture
 */
interface RetrievableChildrenInterface
{
    /**
     * Returns an array of Viewmodels with captureTo value $capture
     *
     * @param string $capture
     * @param bool $recursive search recursive through children, default true
     * @return array
     */
    public function getChildrenByCaptureTo($capture, $recursive = true);
}
