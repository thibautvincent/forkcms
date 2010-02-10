<?php

/**
 * BackendUser
 *
 * The class below will handle all stuff relates to the current authenticated user
 *
 * @package		backend
 * @subpackage	core
 *
 * @author 		Tijs Verkoyen <tijs@netlash.com>
 * @author		Davy Hellemans <davy@netlash.com>
 * @since		2.0
 */
class BackendUser
{
	/**
	 * The group id
	 *
	 * @var	int
	 */
	private $groupId;


	/**
	 * Is the user-object a valid one? As in: is the user authenticated
	 *
	 * @var	bool
	 */
	private $isAuthenticated = false;


	/**
	 * Is the authenticated user a god?
	 *
	 * @var	bool
	 */
	private $isGod = false;


	/**
	 * Last timestamp the user logged in
	 *
	 * @var	int
	 */
	private $lastLoggedInDate;


	/**
	 * The session id for the user
	 *
	 * @var	string
	 */
	private $sessionId;


	/**
	 * The secret key for the user
	 *
	 * @var	string
	 */
	private $secretKey;


	/**
	 * All settings
	 *
	 * @var	array
	 */
	private $settings = array();


	/**
	 * The users id
	 *
	 * @var	int
	 */
	private $userId;


	/**
	 * The username
	 *
	 * @var	string
	 */
	private $username;


	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	int[optional] $userId
	 */
	public function __construct($userId = null)
	{
		// if a userid is given we will load the user in this object
		if($userId !== null) $this->loadUser($userId);
	}


	/**
	 * Get groupid
	 *
	 * @return	int
	 */
	public function getGroupId()
	{
		return $this->groupId;
	}


	/**
	 * Get last logged in date
	 *
	 * @return	int
	 */
	public function getLastloggedInDate()
	{
		return $this->lastLoggedInDate;
	}


	/**
	 * Get sessionId
	 *
	 * @return	string
	 */
	public function getSessionId()
	{
		return $this->sessionId;
	}


	/**
	 * Get a setting
	 *
	 * @return	mixed
	 * @param	string $key
	 * @param	mixed[optional] $defaultValue
	 */
	public function getSetting($key, $defaultValue = null)
	{
		// redefine
		$key = (string) $key;

		// if the value isn't present we should set a defaultvalue
		if(!isset($this->settings[$key])) $this->setSetting($key, $defaultValue);

		// return
		return $this->settings[$key];
	}


	/**
	 * Get secretkey
	 *
	 * @return	string
	 */
	public function getSecretKey()
	{
		return $this->secretKey;
	}


	/**
	 * Get all settings at once
	 *
	 * @return	array
	 */
	public function getSettings()
	{
		return (array) $this->settings;
	}


	/**
	 * Get userid
	 *
	 * @return	int
	 */
	public function getUserId()
	{
		return $this->userId;
	}


	/**
	 * Get username
	 *
	 * @return	string
	 */
	public function getUsername()
	{
		return $this->username;
	}


	/**
	 * Is the current userobject a authenticated user?
	 *
	 * @return	bool
	 */
	public function isAuthenticated()
	{
		return $this->isAuthenticated;
	}


	/**
	 * Is the current user a God?
	 *
	 * @return	bool
	 */
	public function isGod()
	{
		return $this->isGod;
	}


	/**
	 * Load a user
	 *
	 * @return	void
	 * @param	int $userId
	 */
	public function loadUser($userId)
	{
		// redefine
		$userId = (int) $userId;

		// get database instance
		$db = BackendModel::getDB();

		// get user-data
		$userData = (array) $db->getRecord('SELECT u.id, u.group_id, u.username, u.is_god,
											us.session_id, us.secret_key, UNIX_TIMESTAMP(us.date) AS date
											FROM users AS u
											LEFT OUTER JOIN users_sessions AS us ON u.id = us.user_id AND us.session_id = ?
											WHERE u.id = ?
											LIMIT 1;',
											array(SpoonSession::getSessionId(), $userId));

		// if there is no data we have to destroy this object, I know this isn't a realistic situation
		if(empty($userData)) throw new BackendException('user ('. $userId .') can\'t be loaded.')

		// set properties
		$this->setUserId($userData['id']);
		$this->setGroupId($userData['group_id']);
		$this->setUsername($userData['username']);
		$this->setSessionId($userData['session_id']);
		$this->setSecretKey($userData['secret_key']);
		$this->setLastloggedInDate($userData['date']);
		$this->isAuthenticated = true;
		$this->isGod = (bool) ($userData['is_god'] == 'Y');

		// get settings
		$settings = (array) $db->getPairs('SELECT us.name, us.value
											FROM users_settings AS us
											WHERE us.user_id = ?;',
											array($userId));

		// loop settings and store them in the object
		foreach($settings as $key => $value) $this->settings[$key] = unserialize($value);
	}


	/**
	 * Set groupid
	 *
	 * @return	void
	 * @param	int $value
	 */
	private function setGroupId($value)
	{
		$this->groupId = (int) $value;
	}


	/**
	 * Set last logged in date
	 *
	 * @return	void
	 * @param	int $value
	 */
	private function setLastloggedInDate($value)
	{
		$this->lastLoggedInDate = (int) $value;
	}


	/**
	 * Set secretkey
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setSecretKey($value)
	{
		$this->secretKey = (string) $value;
	}


	/**
	 * Set sessionid
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setSessionId($value)
	{
		$this->sessionId = (string) $value;
	}


	/**
	 * Set a setting
	 *
	 * @return	void
	 * @param	string $key
	 * @param	mixed $value
	 */
	public function setSetting($key, $value)
	{
		// redefine
		$key = (string) $key;
		$valueToStore = serialize($value);

		// get db
		$db = BackendModel::getDB(true);

		// store
		$db->execute('INSERT INTO users_settings(user_id, name, value)
						VALUES(?, ?, ?)
						ON DUPLICATE KEY UPDATE value = ?;',
						array($this->getUserId(), $key, $valueToStore, $valueToStore));

		// cache it
		$this->settings[(string) $key] = $value;
	}


	/**
	 * Set userid
	 *
	 * @return	void
	 * @param	int $value
	 */
	private function setUserId($value)
	{
		$this->userId = (int) $value;
	}


	/**
	 * Set username
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setUsername($value)
	{
		$this->username = (string) $value;
	}
}

?>