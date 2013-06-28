<?php
namespace LeproToolkit\Models;

/**
 * Модель профайла лепроюзера
 *
 * @package LeproToolkit\Models
 */
class Profile {
    /**
     * Лепрономер
     */
    public $uid;

    /**
     * Юзернейм
     */
    public $username;

    /**
     * Имя
     */
    public $name;
    /**
     * Пол
     */
    public $gender;
    /**
     * Дата регистрации
     */
    public $regdate;
    /**
     * Таймштамп даты регистрации
     */
    public $regdateTimestamp;
    /**
     * Значение кармы
     */
    public $karma;
    /**
     * Значение рейтинга
     */
    public $rating;
    /**
     * Количество постов
     */
    public $postsCount;
    /**
     * Количество комментов
     */
    public $commentsCount;
    /**
     * Количество подлепр
     */
    public $subSitesCount;
    /**
     * Город
     */
    public $city;
    /**
     * Страна
     */
    public $country;
    /**
     * Картинка в профиле
     */
    public $userpic;
    /**
     * Приглашенные
     */
    public $invited;
    /**
     * Кем приглашен
     */
    public $invitedBy;
    /**
     * Большой рассказ о себе
     */
    public $userstory;
    /**
     * Вес голоса
     */
    public $voteweight;

    /**
     * Голосов в день
     */
    public $votesCount;

    /**
     * Сколько раз избирался ПГ
     */
    public $presidentialTermsCount;

    /**
     * В отставке с (ПГ)
     */
    public $presidentialRetirement;

    /**
     * Сколько раз избирался министром
     */
    public $ministerialTermsCount;

    /**
     * В оставке с (министр)
     */
    public $ministerialRetirement;

    /**
     * Проверяет большой рассказ о себе на наличие подстроки
     *
     * @param $str
     * @return bool
     */
    public function storyContains($str)
    {
        if(strpos($this->userstory, $str))
        {
            return true;
        }

        return false;
    }
}
