<?php
App::uses('UniLogin', 'UniLoginWebservice.Model');
App::uses('PHPUnitUtil', 'Lib');

class TestUniLogin extends UniLogin {

/**
 * Public test double of `parent::_getAuthParameters`.
 *
 */
	public function getAuthParameters() {
		return parent::_getAuthParameters();
	}

/**
 * Public test double of `parent::_convertUserList`.
 *
 */
	public function convertUserList($users) {
		return parent::_convertUserList($users);
	}

/**
 * Public test double of `parent::_convertInstitution`.
 *
 */
	public function convertInstitution($role, $minimal = false) {
		return parent::_convertInstitution($role, $minimal);
	}

/**
 * Public test double of `parent::_convertInstitutionList`.
 *
 */
	public function convertInstitutionList($institutions) {
		return parent::_convertInstitutionList($institutions);
	}

/**
 * Public test double of `parent::_convertRole`.
 *
 */
	public function convertRole($role) {
		return parent::_convertRole($role);
	}

/**
 * Public test double of `parent::_convertUser`.
 *
 */
	public function convertUser($user, $minimal = false) {
		return parent::_convertUser($user, $minimal);
	}

/**
 * Public test double of `parent::_parseDate`.
 *
 */
	public function parseDate($dateString) {
		return parent::_parseDate($dateString);
	}

}

/**
 * UniLogin Test.
 *
 */
class UniLoginTest extends CakeTestCase {

/**
 * Fixtures.
 *
 * @var array
 */
	public $fixtures = [];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->UniLogin = ClassRegistry::init('TestUniLogin');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UniLogin);

		parent::tearDown();
	}

/**
 * Converts a (minimal) user array to a user object.
 *
 * @param array $user User data
 * @return \stdClass User object
 */
	protected function _convertUserMinimal($user) {
		$result = new stdClass();
		$result->Brugerid = $user['uni_login_key'];
		$result->Navn = $user['full_name'];

		return $result;
	}

/**
 * Converts a (minimal) institution array to a institution object.
 *
 * @param array $institution Institution data
 * @return \stdClass Institution object
 */
	protected function _convertInstitutionMinimal($institution) {
		$result = new stdClass();
		$result->Instnr = $institution['uni_login_key'];
		$result->Navn = $institution['name'];

		return $result;
	}

/**
 * Tests `UniLogin::getAuthParameters`.
 *
 * @return void
 */
	public function testGetAuthParameters() {
		$restore = Configure::read('UniLoginWebservice.wsBrugerid');

		Configure::write('UniLoginWebservice.wsBrugerid', 'wsBrugerid');
		Configure::write('UniLoginWebservice.wsPassword', 'wsPassword');

		$expected = [
			'wsBrugerid' => 'wsBrugerid',
			'wsPassword' => 'wsPassword'
		];
		$resut = $this->UniLogin->getAuthParameters();
		$this->assertEquals($expected, $resut);

		Configure::write('UniLoginWebservice', $restore);
	}

/**
 * Tests `UniLogin::convertUserList`.
 *
 * @return void
 */
	public function testConvertUserList() {
		$expected = [];
		$expected[] = [
			'uni_login_key' => '123456',
			'full_name' => 'My Full Name'
		];
		$userList = new stdClass();
		$userList->PersonSimpel = [];

		foreach ($expected as $item) {
			$userList->PersonSimpel[] = $this->_convertUserMinimal($item);
		}
		$result = $this->UniLogin->convertUserList($userList);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::convertUserList`.
 *
 * @return void
 */
	public function testConvertUserListValid() {
		$expected = [
			[
				'uni_login_key' => 'Fe5Echew',
				'full_name' => 'Amalie Jansson'
			],
			[
				'uni_login_key' => 'naJutr4s',
				'full_name' => 'Katrine Juncker'
			]
		];
		$userList = new stdClass();
		$userList->PersonSimpel = [];
		$user = new stdClass();
		$user->Navn = 'Amalie Jansson';
		$user->Brugerid = 'Fe5Echew';
		$userList->PersonSimpel[] = $user;
		$user = new stdClass();
		$user->Navn = 'Katrine Juncker';
		$user->Brugerid = 'naJutr4s';
		$userList->PersonSimpel[] = $user;
		$result = $this->UniLogin->convertUserList($userList);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::convertRole`.
 *
 * @return void
 */
	public function testConvertRoleInvalid() {
		$role = 'unknown';
		$result = $this->UniLogin->convertRole($role);
		$this->assertFalse($result);
	}

/**
 * Tests `UniLogin::convertRole`.
 *
 * @return void
 */
	public function testConvertRoleValid() {
		$mapping = [
			'lærer' => 'teacher',
			'tap' => 'technical / administrative employee',
			'pæd' => 'educator', // Pædagog
			'elev' => 'pupil',
			'stud' => 'student',
			'kursist' => 'anonymous user with limited lifespan',
			'klasse' => 'class',
			'skole' => 'common school login',
			'Instleder' => 'director at institution',
			'Instledelse' => 'board of directors',
			'Brugeradm' => 'user administrator',
			'brugeradm_sup' => 'additional user administrator',
			'Kontakt' => 'contact person at institution',
			'uni_server_adm' => 'UNI-Server administrator',
			'uni_server_indholds_adm' => 'UNI-Server Content administrator',
			'hjpc_ansv' => 'HomePC responsible',
			'hjpc_ansv_a' => 'HomePC responsible for A-leg',
			'hjpc_ansv_p' => 'HomePC responsible for P-leg'
		];
		foreach ($mapping as $role => $expected) {
			$result = $this->UniLogin->convertRole($role);
			$this->assertEquals($expected, $result);
		}
	}

/**
 * Tests `UniLogin::convertInstitution`.
 *
 * @return void
 */
	public function testConvertInstitutionInvalid() {
		$institution = null;
		$result = $this->UniLogin->convertInstitution($institution);
		$this->assertFalse($result);

		$institution = new stdClass();
		$result = $this->UniLogin->convertInstitution($institution);
		$this->assertFalse($result);
	}

/**
 * Tests `UniLogin::convertInstitution`.
 *
 * @return void
 */
	public function testConvertInstitutionMinimalValid() {
		$minimal = true;
		$institution = new stdClass();
		$institution->Instnr = '101001';
		$institution->Navn = 'Name of institution.';
		$expected = [
			'uni_login_key' => '101001',
			'name' => 'Name of institution.'
		];
		$result = $this->UniLogin->convertInstitution($institution, $minimal);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::convertInstitution`.
 *
 * @return void
 */
	public function testConvertInstitutionValid() {
		$institution = new stdClass();
		$institution->Instnr = '101001';
		$institution->Navn = 'Name of institution.';
		$institution->Type = '121';
		$institution->Typenavn = 'Grundskoler';
		$institution->Adresse = 'Address';
		$institution->Bynavn = 'City name';
		$institution->Postnr = 'Zip code';
		$institution->Telefonnr = 'Phone number';
		$institution->Faxnr = 'Fax number';
		$institution->Mailadresse = 'Mail address';
		$institution->Www = 'URL of institution';
		$institution->Hovedinstitutionsnr = '101004';
		$institution->Kommunenr = '123';
		$institution->Kommune = 'Name of municipal';
		$institution->Admkommunenr = '456';
		$institution->Admkommune = 'Name of the administrating municipal';
		$institution->Regionsnr = '1234';
		$institution->Region = 'Name of the region';
		$expected = [
			'uni_login_key' => '101001',
			'name' => 'Name of institution.',
			'type' => '121',
			'type_name' => 'Grundskoler',
			'address' => 'Address',
			'city' => 'City name',
			'zip_code' => 'Zip code',
			'phone_number' => 'Phone number',
			'fax_number' => 'Fax number',
			'mail_address' => 'Mail address',
			'website' => 'URL of institution',
			'parent_institution_uni_login_key' => '101004',
			'municipal' => '123',
			'municipal_name' => 'Name of municipal',
			'administrating_municipal' => '456',
			'administrating_municipal_name' => 'Name of the administrating municipal',
			'region' => '1234',
			'region_name' => 'Name of the region',
		];
		$result = $this->UniLogin->convertInstitution($institution);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::convertUserList`.
 *
 * @return void
 */
	public function testConvertInstitutionList() {
		$expected = [];
		$expected[] = [
			'uni_login_key' => '123456',
			'name' => 'Name'
		];
		$institutionList = new stdClass();
		$institutionList->InstitutionSimpel = [];

		foreach ($expected as $item) {
			$institutionList->InstitutionSimpel[] = $this->_convertInstitutionMinimal($item);
		}
		$result = $this->UniLogin->convertInstitutionList($institutionList);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::convertUser`.
 *
 * @return void
 */
	public function testConvertUserInvalid() {
		$user = null;
		$result = $this->UniLogin->convertUser($user);
		$this->assertFalse($result);

		$user = new stdClass();
		$result = $this->UniLogin->convertUser($user);
		$this->assertFalse($result);

		$user = new stdClass();
		$user->brugerid = '101001';
		$user->navn = 'Svend Hansen';
		$result = $this->UniLogin->convertUser($user);
		$this->assertFalse($result);
	}

/**
 * Tests `UniLogin::convertUser`.
 *
 * @return void
 */
	public function testConvertUserMinimalValid() {
		$minimal = true;
		$user = new stdClass();
		$user->Brugerid = '101001';
		$user->Navn = 'Svend Hansen';
		$expected = [
			'uni_login_key' => '101001',
			'full_name' => 'Svend Hansen'
		];
		$result = $this->UniLogin->convertUser($user, $minimal);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::convertUser`.
 *
 * @return void
 */
	public function testConvertUserValid() {
		$user = new stdClass();
		$user->Brugerid = '101001';
		$user->Navn = 'Svend Hansen';
		$user->Fornavn = 'Svend';
		$user->Efternavn = 'Hansen';
		$user->SkolekomNavn = 'Svend Hansen12';
		$user->Mailadresse = 'Svend.Hansen12@skolekom.dk';
		$user->Instnr = '101005';
		$user->Funktionsmarkering = 'kursist';
		$user->Foedselsdag = '130597';
		$expected = [
			'uni_login_key' => '101001',
			'full_name' => 'Svend Hansen',
			'first_name' => 'Svend',
			'last_name' => 'Hansen',
			'username' => 'Svend Hansen12',
			'email' => 'Svend.Hansen12@skolekom.dk',
			'school_uni_login_key' => '101005',
			'uni_login_role' => 'kursist',
			'role' => 'anonymous user with limited lifespan',
			'date_of_birth' => '1997-05-13'
		];
		$result = $this->UniLogin->convertUser($user);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests `UniLogin::parseDate`.
 *
 * @return void
 */
	public function testParseDateInvalid() {
		$date = '';
		$result = $this->UniLogin->parseDate($date);
		$this->assertFalse($result);

		$date = 'abc';
		$result = $this->UniLogin->parseDate($date);
		$this->assertFalse($result);
	}

/**
 * Tests `UniLogin::parseDate`.
 *
 * @return void
 */
	public function testParseDateValid() {
		$expected = '2003-02-01';
		$date = '010203';
		$result = $this->UniLogin->parseDate($date);
		$this->assertEquals($expected, $result);

		$expected = '1990-10-30';
		$date = '301090';
		$result = $this->UniLogin->parseDate($date);
		$this->assertEquals($expected, $result);
	}

}
