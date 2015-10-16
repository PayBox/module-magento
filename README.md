# module-magento

Модуль интеграции платформы [Magento](http://magento.com/) с платежной системой [PayBox](http://paybox.kz).

### Инструкция

Для работы модуля необходимо выполнить следующие шаги:

##### 1. Заключить договор с PayBox

Заполнить форму заявки на сайте [PayBox](http://paybox.kz) для получения доступа к личному кабинету PayBox.

##### 2. Установить и настроить модуль модуль 

1. Установка модуля. Загрузить через FTP содержимое каталога `app` из архива в каталог `app` на сервере.
2. В админке Magento выбрать *Система &rarr; Конфигурация* (*System &rarr; Configuration*). В левой колонке в разделе *PayBox Extensions* выбрать *PayBox Payment Module*. На этой странице вы можете ознакомиться с описанием модуля и перейти к настройкам.
3. Настройка модуля. На странице настройки нужно **включить модуль** и **выключить тестовый режим и режим отладки**. **ID продавца и секретный ключ** берутся из [личного кабинета PayBox](https://www.paybox.kz/admin/merchants.php). **Описание заказа** - информация, которая выводится пользователю при оплате на на сайте PayBox.
4. После того, как все настройки будут сохранены, вам будет доступен метод оплаты через систему PayBox.

---

Работа модуля протестирована на Magento CE версии 1.19.2.1 (но также он должен запуститься и на ранних версиях платформы)
