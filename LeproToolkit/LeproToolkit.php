<?php

namespace LeproToolkit;

use LeproToolkit\Parsers\ProfileParser;
use LeproToolkit\Components\Fetcher;
use LeproToolkit\Components\LeproToolkitException;

/**
 * Главный класс для работы с лепрой.
 *
 * @package LeproToolkit
 */
class LeproToolkit {

	public $fetcher = '\LeproToolkit\Components\Fetcher';

	/**
	 * Фетчер
	 * @var Fetcher
	 */
	protected $_fetcher;

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
			throw new \Exception('curl PHP extension not loaded');
		}

		if(!is_numeric($uid))
		{
			throw new LeproToolkitException('Wrong user ID');
		}

		if(strlen($sid) != 32)
		{
			throw new LeproToolkitException('Wrong session ID');
		}

		$this->_fetcher = new $this->fetcher($uid, $sid);
	}

	/**
	 * Ссылка на фетчер
	 *
	 * @return Fetcher
	 */
	protected function getFetcher()
	{
		return $this->_fetcher;
	}

	/**
	 * Возвращает профайл пользователя по его юзернейму
	 *
	 * @param $username
	 * @return mixed Profile
	 */
	public function getProfileByUsername($username)
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
	public function getProfileById($uid)
	{
		$parser = new ProfileParser($this->getFetcher()->fetchProfileById($uid), 'json');
		$profile = $parser->parseProfile();
		return $profile;
	}

	public function getContents($url)
	{
		return $this->getFetcher()->fetchUrl($url);
	}
}
