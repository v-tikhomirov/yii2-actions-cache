<?php

namespace Tikhomirov\Yii2ActionsCache;

interface CacheableControllerInterface
{
    public const DEPENDENCY_USER = 'dependency_user';
    public const DEPENDENCY_GET = 'dependency_get';
    public const DEPENDENCY_POST = 'dependency_post';

    public function getActionsDependencies(): array;

    public function getCacheDurationInSeconds(): int;

    public function getCacheableActions(): array;
}
