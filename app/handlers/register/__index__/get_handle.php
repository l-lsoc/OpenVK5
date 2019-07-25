<?php

/**
* Регистрация
*/
return (function($args) {
    if(isset($args["f"])) {
        $errors = [
            "Такой пользователь уже существует",
            "Такой e-mail уже существует",
            "Условия обслуживания не приняты",
            "Не удалось пройти проверку CAPTCHA",
            "Регистрация закрыта",
            "Регистрация временно не доступна",
        ];
        $error = array_key_exists((int) $args["f"], $errors)? $errors[$args["f"]] : "Неизвестная ошибка доступа";
        
        return ["error" => (object) [
            "type" => "error",
            "desc" => $error,
        ]];
    }
    
    return [];
});
