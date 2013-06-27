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
	 * Лепрономер, от чьего имени будем работать
	 */
	protected $_uid;

	/**
	 * Идентификатор сессии
	 */
	protected $_sid;

	/**
	 * Cсылка на curl
	 */
	protected $_curl;

	public function __construct($uid, $sid)
	{
		$this->_sid = $sid;
		$this->_uid = $uid;

		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_curl, CURLOPT_VERBOSE, 1);
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_curl, CURLOPT_COOKIE, $this->prepareCookies());
	}

	public function __destruct()
	{
		curl_close($this->_curl);
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
	 * Забирает HTML профайла по юзернейму
	 *
	 * @param $username
	 * @return mixed
	 */
	public function fetchProfileByUsername($username)
	{
		return $this->fetchUrl('http://' . $this->baseHost . '/users/' . trim($username));
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
	 * @throws LeproToolkitException
	 */
	public function fetchUrl($url)
	{
		try {
			curl_setopt($this->_curl, CURLOPT_URL, $url);
			$response = curl_exec($this->_curl);
			$url = curl_getinfo($this->_curl, CURLINFO_EFFECTIVE_URL);
		} catch(\Exception $e)
		{
			// todo: log?
		}

		if(!$response)
		{
			throw new LeproToolkitException(curl_error($this->_curl), 500);
		}

		if($this->is404($response))
		{
			throw new LeproToolkitException('404', 404);
		}

		if($this->isOffline($response))
		{
			throw new LeproToolkitException('Leprosorium is offline', 503);
		}

		if($this->isNoSuchSubSite($url))
		{
			throw new LeproToolkitException('No such subsite', 404);
		}

		if($this->isAccessForbidden($url))
		{
			throw new LeproToolkitException('Access forbidden', 403);
		}

		return $response;
	}

	/**
	 * Проверка на 404
	 *
	 * @param $response
	 * @return bool
	 */
	protected function is404($response)
	{
		if(strpos($response, 'ДОБРО ПОЖАЛОВАТЬ НА СТРАНИЦУ 404! ')) {
			return true;
		}

		return false;
	}

	/**
	 * Проверка на балет
	 *
	 * @param $response
	 * @return bool
	 */
	protected function isOffline($url)
	{
		if(parse_url($url, PHP_URL_PATH == '/off/index.html')) {
			return true;
		}

		return false;
	}

	/**
	 * Проверка на существование подлепры
	 *
	 * @param $url
	 * @return bool
	 */
	protected function isNoSuchSubSite($url)
	{
		if(parse_url($url, PHP_URL_PATH) == '/missing.html') {
			return true;
		}

		return false;
	}

	/**
	 * Проверка на закрытость подлепры
	 *
	 * @param $response
	 * @return bool
	 */
	protected function isAccessForbidden($url)
	{
		if(parse_url($url, PHP_URL_PATH) == '/gtfo/') {
			return true;
		}

		return false;
	}
}
