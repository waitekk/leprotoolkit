<?php

namespace LeproToolkit\Parsers;

use LeproToolkit\Components\LeproToolkitException;
use LeproToolkit\Models\Profile;

/**
 * Парсер профайлов
 *
 * @package LeproToolkit\Parsers
 */
class ProfileParser {
	/**
	 * HTML-код ответа
	 * @var
	 */
	protected $_response;

	/**
	 * DOM-представление ответа
	 * @var
	 */
	protected $_dom;

	/**
	 * Тип ответа — html/json
	 * @var null
	 */
	protected $_type;

	/**
	 * Модель профайла
	 * @var
	 */
	protected $_profile;

	public function __construct($response, $type = 'html')
	{
		if($response == '')
		{
			throw new LeproToolkitException('No data retrieved'); // todo: перенести исключение в фетчер?
		}

		$this->_response = $response;
		$this->_type = $type;
	}

	/**
	 * Ссылка на DOM-представление
	 *
	 * @return DOMDocument
	 */
	protected function getDom()
	{
		if(null === $this->_dom)
		{
			$this->_dom = new DOMDocument;
			$this->_dom->loadHtml($this->_response);
		}

		return $this->_dom;
	}

	/**
	 * Разбирает ответ и возвращает профайл
	 *
	 * @return mixed Profile
	 */
	public function parseProfile()
	{
		if($this->_type == 'json')
		{
			$this->parseApi();
		} else {
			// todo: implement
		}

		return $this->_profile;
	}

	/**
	 * Разбирает JSON-ответ от API лепропанели
	 */
	public function parseApi()
	{
		$json = json_decode($this->_response);

		if($json->status == 'ERR')
		{
			throw new LeproToolkitException($json->message);
		}

		$this->_profile = new Profile();

		$this->_profile ->uid              = $json->uid;
		$this->_profile ->username         = $json->login;

		$this->_profile ->regdate          = $json->regdate;
		$this->_profile ->regdateTimestamp = $json->regdate_timestamp;

		$this->_profile ->karma            = $json->karma;
		$this->_profile ->rating           = $json->rating;
		$this->_profile ->voteweight       = $json->voteweight;

		$this->_profile ->postsCount       = $json->posts;
		$this->_profile ->commentsCount    = $json->comments;

		$this->_profile ->invitedBy        = $json->invited_by;
	}
}