<?php

namespace Tikhomirov\Yii2ActionsCache;

use Exception;
use Tikhomirov\Yii2ActionsCache\exceptions\InvalidActionCacheConfigException;
use Tikhomirov\Yii2ActionsCache\exceptions\CacheableControllerException;
use Yii;
use yii\base\InvalidRouteException;
use yii\helpers\ArrayHelper;

/**
 * @method array getCacheableActions()
 * @see CacheableControllerInterface::getCacheableActions()
 */
trait CacheableControllerTrait
{
    private static int $defaultCacheDuration = 7200; // 2 часа
    private static string $cacheKeyTemplate = 'action_%s_%s_%s';
    private static string $invalidDependenciesMessageTemplate = 'Неверная конфигурация зависимостей кеширования для %s';
    private static string $invalidDurationMessageTemplate = 'Неверная конфигурация длительности кеширования для %s';
    private static string $invalidDependencyMessageTemplate = 'Зависимость %s не определена';

    /**
     * @throws CacheableControllerException
     * @throws InvalidActionCacheConfigException
     * @throws InvalidRouteException
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

    private function canCacheAction(string $action): bool
    {
        return array_key_exists($action, $this->getCacheableActions());
    }

    /**
     * @throws InvalidActionCacheConfigException
     */
    private function getDuration(string $action): int
    {
        $key = $action . '.' . CacheableControllerInterface::ATTRIBUTE_DURATION;
        $result = ArrayHelper::getValue($this->getCacheableActions(), $key, self::$defaultCacheDuration);
        if (!is_int($result)) {
            $message = sprintf(self::$invalidDurationMessageTemplate, $action);
            throw new InvalidActionCacheConfigException($message);
        }

        return $result;
    }

    /**
     * @throws CacheableControllerException
     * @throws InvalidActionCacheConfigException
     * @throws Exception
     */
    private function getCacheKey(string $action): string
    {
        $dependenciesData = array_map(function (string $dependency) {
            return $this->getDependencyData($dependency);
        }, $this->getDependencies($action));

        $key = sprintf(
            self::$cacheKeyTemplate,
            get_class($this),
            $action,
            implode('_', $dependenciesData)
        );
        return crc32($key);
    }

    /**
     * @return string[]
     * @throws InvalidActionCacheConfigException
     */
    private function getDependencies(string $action): array
    {
        $key = $action . '.' . CacheableControllerInterface::ATTRIBUTE_DEPENDENCIES;
        $result = ArrayHelper::getValue($this->getCacheableActions(), $key, []);
        if (!is_array($result)) {
            $message = sprintf(self::$invalidDependenciesMessageTemplate, $action);
            throw new InvalidActionCacheConfigException($message);
        }

        return $result;
    }

    /**
     * @throws CacheableControllerException
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
                $message = sprintf(self::$invalidDependencyMessageTemplate, $dependency);
                throw new CacheableControllerException($message);
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
