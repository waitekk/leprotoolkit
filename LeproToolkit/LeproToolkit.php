<?php

namespace LeproToolkit;

use LeproToolkit\Parsers\ProfileParser;
use LeproToolkit\Fetcher;

/**
 * Главный класс для работы с лепрой.
 *
 * @package LeproToolkit
 */
class LeproToolkit {
    /**
     * Ссылка на ресурс с курлом
     */
    protected $_curl;

    /**
     * Фетчер
     * @var Fetcher
     */
    protected $_fetcher;

    /**
     * Идентификатор сессии
     */
    protected $_sid;

    /**
     * Лепрономер, от чьего имени будем работать
     */
    protected $_uid;

	/**
     * Запуск
     *
	 * @param $uid integer Лепрономер
	 * @param $sid string Идентификатор сессии пользователя
	 *
	 * @throws \Exception
	 */
	public function __construct($uid, $sid)
	{
		if(!extension_loaded('curl'))
		{
			throw new \Exception('Не загружен curl');
		}

		if(!is_numeric($uid))
		{
			throw new \Exception('Неверно заданы идентификатор пользователя');
		}

		$this->_uid = $uid;

		if(strlen($sid) != 32)
		{
			throw new \Exception('Неверно задан идентификатор сессии');
		}

		$this->_sid = $sid;

		$this->initCurl();
	}

    /**
     * Гарантированно прибиваем курл
     */
    public function __destruct()
    {
        curl_close($this->_curl);
    }

    /**
     * Инициализирует курл
     */
    protected function initCurl()
	{
        if(null === $this->_curl)
        {
            $this->_curl = curl_init();
            curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->_curl, CURLOPT_VERBOSE, 1);
            curl_setopt($this->_curl, CURLOPT_COOKIE, $this->prepareCookies());
        }

        return $this->_curl;
	}

    /**
     * Готовим нужные куки
     * @return string
     */
    protected function prepareCookies()
    {
        return 'uid=' . $this->_uid . '; sid=' . $this->_sid;
    }

    /**
     * Ссылка на фетчер
     *
     * @return Fetcher
     */
    protected function getFetcher()
    {
        if(null === $this->_fetcher)
        {
            $this->_fetcher = new Fetcher($this->_curl);
        }

        return $this->_fetcher;
    }

    /**
     * Возвращает профайл пользователя по его юзернейму
     *
     * @param $username
     * @return mixed Profile
     */
    public function getUserProfileByUsername($username)
    {
        $parser = new ProfileParser($this->getFetcher()->fetchProfileByUsername($username));
        $profile = $parser->parseProfile();
        return $profile;
    }

    /**
     * Возвращает профайл пользователя по лепрономеру
     *
     * @param $uid
     * @return mixed Profile
     */
    public function getUserProfileById($uid)
    {
        $parser = new ProfileParser($this->getFetcher()->fetchProfileById($uid), 'json');
        $profile = $parser->parseProfile();
        return $profile;
    }
}
