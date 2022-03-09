<?php

namespace Tikhomirov\Yii2ActionsCache;

use Exception;
use Yii;
use yii\base\InvalidRouteException;
use yii\helpers\ArrayHelper;

trait CacheableControllerTrait
{
    private static string $cacheKeyTemplate = 'action_%s_%s_%s';
    private static int $defaultCacheDuration = 7200; // 2 часа

    /**
     * @throws Exception
     */
    public function runAction($id, $params = [])
    {
        $cache = Yii::$app->getCache();
        if (!$cache
            || !method_exists($this, 'getCacheableActions')
            || !$this->canCacheAction($id)) {
            return parent::runAction($id, $params);
        }

        return $cache->getOrSet($this->getCacheKey($id), function () use ($id, $params) {
            return parent::runAction($id, $params);
        }, $this->getDuration($id));
    }

    private function getCacheDuration(): int
    {
        if (!method_exists($this, 'getCacheDurationInSeconds')) {
            return self::$defaultCacheDuration;
        }

        return $this->getCacheDurationInSeconds();
    }

    /**
     * @throws Exception
     */
    private function getCacheKey(string $action): ?string
    {
        if (!method_exists($this, 'getActionsDependencies')) {
            return null;
        }

        $dependencies = ArrayHelper::getValue($this->getActionsDependencies(), $action);
        if (!is_array($dependencies)) {
            return null;
        }

        $dependenciesData = array_map(function (string $dependency) {
            return $this->getDependencyData($dependency);
        }, $dependencies);

        $key = sprintf(
            self::$cacheKeyTemplate,
            get_class($this),
            $action,
            implode('_', $dependenciesData)
        );
        return crc32($key);
    }

    /**
     * @throws Exception
     */
    private function getDependencyData(string $dependency): string
    {
        switch ($dependency) {
            case CacheableControllerInterface::DEPENDENCY_USER:
                return $this->getUserDependencyData();
            case CacheableControllerInterface::DEPENDENCY_GET:
                return $this->getGetDependencyData();
            case CacheableControllerInterface::DEPENDENCY_POST:
                return $this->getPostDependencyData();
            default:
                throw new Exception("Зависимость {$dependency} не определена");
        }
    }

    private function getUserDependencyData(): string
    {
        return strval(Yii::$app->getUser()->getId());
    }

    private function getGetDependencyData(): string
    {
        return json_encode(Yii::$app->request->get());
    }

    private function getPostDependencyData(): string
    {
        return json_encode(Yii::$app->request->post());
    }
}
