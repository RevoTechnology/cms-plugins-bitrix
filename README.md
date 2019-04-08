# Рево Рассрочка: Плагин для 1С Битрикс 

Плагин позволяет подключить платежную систему 
рассрочки Рево к интернет-магазину битрикс. 

Модуль поставляется в кодировке Windows-1251 для совместимости с Маркетплейсом 1С-Битрикс.

## Платежная система

Модуль при установке добавляет платежную систему в интернет-магазин битрикс.
При выборе платежной системы в стандартном компоненте оформления заказа модуль 
открывает окно регистрации в РЕВО.

При оформлении заказа и переходе к оплате модуль открывает окно оформления заказа в РЕВО.

## Компонент Товар Детально

В стандартном компоненте детальной страницы товара модуль добавляет ссылку "Оформить рассрочку".
При нажатии на ссылку открывается окно получения лимита в РЕВО.

- [ ] добавить цену ежемесячного платежа
- [ ] добавить после получения лимита переход к оформлению заказа в битрикс с предустановленной платежной системой
 

## Запуск тестов

```
apt install phpunit
cd $MODULE_DIR
phpunit
```