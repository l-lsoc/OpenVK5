<?php

/**
* Вход
*/
return (function($args) {
    if(isset($args["f"])) {
        if($args["f"] == 100) {
            return ["error" => (object) [
                "type" => "info",
                "desc" => "Регистрация завершена успешно, теперь вы можете войти",
            ]];
        }
    
        $errors = [
            "Неверный e-mail или пароль",
        ];
        $error = array_key_exists((int) $args["f"], $errors)? $errors[$args["f"]] : "Неизвестная ошибка доступа";
        
        return ["error" => (object) [
            "type" => "error",
            "desc" => $error,
        ]];
    }
    
    return [];
});
