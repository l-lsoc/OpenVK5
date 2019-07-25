Роутер
----

## Аннотация
Роутер отвечает за передачу управления, в зависимости от того, куда идёт запрос. Роутер является центральным компонентом системы.

## Принцип работы
Роутер берёт адрес страницы из $_SERVER["QUERY_STRING"]. То есть, из всего, что идёт после ? в адресе страницы.
Например: example.org/?/example ("/example" -> адрес страницы).
После чего адрес расщепляется на три компонента - контроллер, действие и параметры. Имя контроллера является первой последовательностью *обычных* символов после /. Имя дейтсвия передаётся в GET-параметре act, а параметрами являются все GET-параметры, кроме act.
Пример: example.org/?/user/bla/bla/bla&act=view&id=20&x=5 ("user" -> контроллер, "view" -> действие, [id => 20, x => 5] -> параметры).
Для контроллера и действия предусмотрены значения по-умолчанию: `__index__`.
Пример: example.org/?/ ("__index__" -> контроллер, "__index__" -> действие, [] -> параметры).

### Поиск обработчика
По умолчанию роутер ищет обработчик здесь: <корень проекта>/app/handlers/<контроллер>/<действие>