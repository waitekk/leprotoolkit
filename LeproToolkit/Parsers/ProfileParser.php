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
	 * Ссылка на XPath-парсер
	 * @var \DOMXPath
	 */
	protected $_xpath;

	/**
	 * Тип ответа — html/json
	 * @var null
	 */
	protected $_responseType;

	/**
	 * Модель профайла
	 * @var \LeproToolkit\Models\Profile
	 */
	protected $_profile;

	public function __construct($response, $type = 'html')
	{
		if($response == '')
		{
			throw new LeproToolkitException('No data retrieved'); // todo: перенести исключение в фетчер?
		}

		$this->_response = $response;
		$this->_responseType = $type;
	}

	/**
	 * Ссылка на DOM-представление
	 *
	 * @return \DOMDocument
	 */
	protected function getDom()
	{
		if(null === $this->_dom)
		{
			$this->_dom = new \DOMDocument;

            // подавление ругани на некорректный HTML
			@$this->_dom->loadHtml($this->_response);
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
		$this->_profile = new Profile();

		if($this->_responseType == 'json')
		{
			$this->parseJson();
		} else {
			$this->parseHtml();
		}

		return $this->_profile;
	}

	/**
	 * Разбирает JSON-ответ от API лепропанели
	 */
	protected function parseJson()
	{
		$json = json_decode($this->_response);

		if($json->status == 'ERR')
		{
			throw new LeproToolkitException($json->message);
		}

		$this->_profile->uid				= $json->uid;
		$this->_profile->username			= $json->login;

		$this->_profile->regdate			= $json->regdate;
		$this->_profile->regdateTimestamp	= $json->regdate_timestamp;

		$this->_profile->karma				= $json->karma;
		$this->_profile->rating				= $json->rating;
		$this->_profile->voteweight			= $json->voteweight;

		$this->_profile->postsCount			= $json->posts;
		$this->_profile->commentsCount		= $json->comments;

		$this->_profile->invitedBy			= $json->invited_by;
	}

	protected function parseHtml()
	{
		try {
			$this->_xpath = new \DOMXPath($this->getDom());
		} catch(\Exception $e)
		{
			// todo: log
		}

		$this->_profile->uid				= $this->extractUid();
		$this->_profile->username			= $this->extractUsername();

		$this->_profile->name				= $this->extractName();
		$this->_profile->city				= $this->extractCity();
		$this->_profile->country			= $this->extractCountry();
        $this->_profile->userpic			= $this->extractUserpic();

		$this->_profile->karma				= $this->extractKarma();
		$this->_profile->voteweight			= $this->extractVoteWeight();
		$this->_profile->votesCount			= $this->extractVoteCount();
	}

	/**
	 * Вычленяет лепрономер
	 *
	 * @return integer
	 */
	protected function extractUid()
	{
		return $this->_xpath->query('//*[@id="uservote"]/div')->item(0)->getAttribute('uid');
	}

    /**
     * Вычленяет юзернейм
     *
     * @return string
     */
    protected function extractUsername()
	{
		return trim( $this->_xpath->query('//*[contains(@class, "username")]/a')->item(0)->textContent );
	}

    /**
     * Вычленяет имя
     *
     * @return string
     */
    protected function extractName()
	{
		return trim( $this->_xpath->query('//*[contains(@class, "userbasicinfo")]/h3')->item(0)->textContent );
	}

    /**
     * Вычленяет город
     *
     * @return string
     */
    protected function extractCity()
	{
		$geo = $this->extractGeo();
		return trim( $geo[1] );
	}

    /**
     * Вычленяет страну
     *
     * @return string
     */
    protected function extractCountry()
	{
		$geo = $this->extractGeo();
		return trim( $geo[0] );
	}

    /**
     * Вычленяет геоданные
     *
     * @return array
     */
    protected function extractGeo()
	{
		return explode( ",", $this->_xpath->query('//*[contains(@class, "userego")]')->item(0)->textContent );
	}

    /**
     * Вычленяет значение кармы
     *
     * @return integer
     */
    protected function extractKarma()
	{
		return trim( $this->_xpath->query('//*[contains(@class, "rating")]/em')->item(0)->textContent );
	}

    /**
     * Вычленяет информацию о голосах
     *
     * @return array
     */
    protected function extractStats()
	{
		return explode( 'Голосов' ,trim( $this->_xpath->query('//*[contains(@class, "uservoterate")]')->item(0)->textContent) );
	}

    /**
     * Вычленяет вес голоса
     *
     * @return int
     */
    protected function extractVoteWeight()
	{
		$stat = $this->extractStats();
		return (int) preg_replace('/\D/', '', $stat[0]);
	}

    /**
     * Вычленяет количество голосов в день
     *
     * @return int
     */
    protected function extractVoteCount()
	{
		$stat = $this->extractStats();
		return (int) preg_replace('/\D/', '', $stat[1]);
	}

    /**
     * Вычленяет URL юзерпика
     * @return string
     */
    protected function extractUserpic()
    {
        $img = $this->_xpath->query('//*[contains(@class, "userpic")]/tbody/tr/td/img')->item(0);

        // если юзерпика нет, элемента img внутри таблицы нет вообще
        if($img != null)
        {
            return $img->getAttribute('src');
        }

        return '';
    }
}
