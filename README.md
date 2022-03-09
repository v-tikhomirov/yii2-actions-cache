## Controller actions cache for Yii2

### Описание
Пакет позволяет кешировать действия контроллеров с учетом зависимостей:
* Пользователь
* GET-параметры
* POST-параметры и устанавливать время кеширования для каждого action отдельно

### Подключение
* В контроллере подключите CacheableControllerTrait и имплементируйте интерфейс
  CacheableControllerInterface
* Реализуйте метод getCacheableActions()
* Он должен возвращать массив, где ключами будут методы, а параметрами - массивы, содержащие
  список зависимостей и время кеширования
  
### Пример
<pre>
public function getCacheableActions(): array
{
  return [
      'action' =>
          CacheableControllerInterface::ATTRIBUTE_DEPENDENCIES => [ // Зависимости
              CacheableControllerInterface::DEPENDENCY_USER, // От пользователя
              CacheableControllerInterface::DEPENDENCY_GET, // От GET-параметров
              CacheableControllerInterface::DEPENDENCY_POST, // От POST-параметров
          ],
          CacheableControllerInterface::ATTRIBUTE_DURATION => 7200, // Установка времени кеширования в секундах
      'another-action' => [], // Если нет зависимостей и устраивает стандартное время кеширования в 2 часа
  ];
}
</pre>
