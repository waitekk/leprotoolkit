<?php

namespace LeproToolkit\Components;

/**
 * Обертка над курлом, стягивающая с лепры необходимые странички.
 * Распознает различные ошибки в этом нелегком деле.
 *
 * @package LeproToolkit
 */
class Fetcher {

    /**
     * Основной домен
     */
    public $baseHost = 'leprosorium.ru';

    /**
     * Подлепра
     */
    public $subSite = '';

    /**
     * Cсылка на curl
     */
    protected $_curl;

    public function __construct($curl)
    {
        $this->_curl = $curl;
    }

    /**
     * Забирает HTML профайла по юзернейму
     *
     * @param $username
     * @return mixed
     */
    public function fetchProfileByUsername($username)
    {
        return $this->fetchUrl('http://' . $this->baseHost . '/users/' . $username);
    }

    /**
     * Забирает JSON профайла через API лепропанели
     *
     * @param $uid
     * @return mixed
     */
    public function fetchProfileById($uid)
    {
        return $this->fetchUrl('http://' . $this->baseHost . '/api/lepropanel/' . $uid);
    }

    /**
     * Основная функция обращения к лепре
     *
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    public function fetchUrl($url)
    {
        try {
            curl_setopt($this->_curl, CURLOPT_URL, $url);
            $result = curl_exec($this->_curl);
            $redirect = curl_getinfo($this->_curl, CURLINFO_EFFECTIVE_URL);
        } catch(\Exception $e)
        {
            // todo: log?
        }

        if(strpos($result, 'Просто у вас нет доступа')) {
            throw new LeproToolkitException('Access forbidden');
        }

        if(strpos($result, 'Дело в том, что такого сайта еще не существует.')) {
            throw new LeproToolkitException('No such subsite');
        }

        if(strpos($result, 'ДОБРО ПОЖАЛОВАТЬ НА СТРАНИЦУ 404! ')) {
            throw new LeproToolkitException('404');
        }

        if($redirect == 'http://' . $this->baseHost . '/off/index.html')
        {
            throw new LeproToolkitException('Leprosorium is offline');
        }

        return $result;
    }
}
