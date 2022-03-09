<?php

namespace Tikhomirov\Yii2ActionsCache;

interface CacheableControllerInterface
{
    public const ATTRIBUTE_DEPENDENCIES = 'dependencies';
    public const ATTRIBUTE_DURATION = 'duration';
    public const DEPENDENCY_USER = 'dependency_user';
    public const DEPENDENCY_GET = 'dependency_get';
    public const DEPENDENCY_POST = 'dependency_post';

    /**
     * @return array
     */
    public function getCacheableActions(): array;
}
