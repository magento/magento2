<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Form\Annotation;

/**
 * Stub class for backwards compatibility.
 *
 * Since PHP 7 adds "object" as a reserved keyword, we can no longer have a class
 * named that and retain PHP 7 compatibility. The original class has been
 * renamed to "Instance", and this class is now an extension of it. It raises an
 * E_USER_DEPRECATED to warn users to migrate.
 *
 * @deprecated
 * @Annotation
 */
class Object extends Instance
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $data)
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\Instance,'
                . ' and the annotation @Instance or @Annotation\Instance',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($data);
    }
}
