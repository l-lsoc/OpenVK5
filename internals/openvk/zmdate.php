<?php
/*
ISC License

Copyright (c) 2018, Unionity

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
*/

/**
* Заменяет английские временные единицы на русские
* @param {string} - строка с датой, полученной из date()
* @see date()
*/
function rusdate(string $datestring): string
  {
    return preg_replace([
      "/January/",
      "/February/",
      "/March/",
      "/April/",
      "/May/",
      "/June/",
      "/July/",
      "/August/",
      "/September/",
      "/October/",
      "/November/",
      "/December/",
    ], [
      "января",
      "февраля",
      "марта",
      "апреля",
      "мая",
      "июня",
      "июля",
      "августа",
      "сентября",
      "октября",
      "ноября",
      "декабря"
    ], $datestring, 1);
  }
/**
* Возвращает относительное время на руссокм
* @param {int} date - дата в UNIX
*/
function zmdate(int $date): string
  {
    $text ="";
    $diff = round(time() - $date);
    if($diff < 0) return null;
    
    $diff = $diff / 86400;
    if($diff > 2) return rusdate(implode(" в ", [date("d F Y ",$date), date(" H:i",$date)]));
    if($diff > 1) return rusdate("вчера в ".date("H:i",$date));
    
    $diff = round((time() - $date) / 60);
    if($diff > 60) {
        if((int) date("H") < (int) date("H",$date)) return rusdate("вчера в ".date("H:i",$date));
        return rusdate("сегодня в ".date("H:i",$date));
    }
    
    return $diff === 5 ? "ровно 5 минут назад" : "$diff минут назад";
  }
  
function zmbd(int $date): string { return zmdate($date); }
function zmdateapi(int $date): string { return zmdate($date); }
function zmd(int $date): string { return "Сервер времени недоступен: E22VR-SKA-I-OVKCI"; } 
