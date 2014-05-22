<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi User Registration Parser
 *
 * Creates a User entity from registration details, hashes the password
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Entity\User;
use Ushahidi\Tool\Parser;
use Ushahidi\Tool\Hasher;
use Ushahidi\Exception\ParserException;

class Ushahidi_Parser_User_Register implements Parser
{
	private $hasher;

	public function __construct(Hasher $hasher)
	{
		$this->hasher = $hasher;
	}

	public function __invoke(Array $data)
	{
		$valid = Validation::factory($data)
			->rules('csrf', array(
					array('not_empty'),
					array('Security::check')
				))
			->rules('email', array(
					array('not_empty'),
				))
			->rules('verify_email', array(
					array('matches', array(':validation', ':field', 'email')),
				))
			->rules('username', array(
					array('not_empty'),
				))
			->rules('password', array(
					array('not_empty'),
					array('min_length', array(':value', 7)),
					array('max_length', array(':value', 72)), // Bcrypt max length is 72
					// NOTE: Password should allow ANY character at all. Do not limit to alpha numeric or alpha dash.
				));

		if (!$valid->check())
		{
			throw new ParserException("Failed to parse user registration", $valid->errors('user'));
		}

		$data['password'] = $this->hasher->hash($data['password']);

		return new User($data);
	}
}
