<?php

namespace FAC\UserBundle\tests\Controller;

use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Utils\TestingUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase {

    public $email         = '';
    public $password      = '';
    public $client_secret  = '5qtx325lby4go0gk0gscookwwwo800ssg0wkwwwscc8w8o0c8c';
    public $public_id      = '1_1l60gilgsin4cwcs0kwcs0k4w48s044cggw0o4s40cg84c4ogk';

    public $bundle         = "UserBundle";
    public $controller     = "UserController";

    private $test_data = array(
        'firstName'      => '',
        'lastName'       => '',
        'email'          => '',
        'password'       => '',
    );

    public function testShowAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "showAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/admin/user/{id_user}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array('id_user' => '');
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        ### START test cases configuration ###

        $response_login = $this->loginAdmin($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];

        $count = 0;
        $id    = "xxx";
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id_user'] = $id;
        $test_cases[$count]['msg'] = "Get by id not numeric check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $id = 0;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id_user'] = $id;
        $test_cases[$count]['msg'] = "Get by id not valid check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $id = "1000000";
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id_user'] = $id;
        $test_cases[$count]['msg'] = "Get by id not existing check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 404;

        $count++;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id_user'] = $id;
        $test_cases[$count]['msg'] = "Get by id check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        ### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testCreateAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        //$this->setByParameters($configs['client']);

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "createAction";

        $configs['header'] = array();
        $configs['method'] = "POST";
        $configs['route'] = "/public/signup/worker";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array();
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $clientIdFake = "3_2mn6n0egv4w0sco00o4gwc0oo0gscokc4ssowkc8c4w04o840g";

        /** @var Client $client */
        $client = $configs['client']->getContainer()->get('FAC\UserBundle\Service\ClientService')->getOneByAttributes(array("id"=>"1"));
        $clientId = $client->getPublicId();

        $count = 0;
        $test_cases[$count]['fields']['email'] = '';
        $test_cases[$count]['fields']['plainPassword'] = 'Test!123';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['msg'] = "Email required";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = 'fastartconsulting.com';
        $test_cases[$count]['fields']['plainPassword'] = 'Test!123';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['msg'] = "Invalid email format";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = TestingUtils::randomValue('user') . '@fastartconsulting.com';
        $test_cases[$count]['fields']['plainPassword'] = '';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['msg'] = 'Password required';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'afslces';
        $test_cases[$count]['msg'] = 'Password too short';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'afsD^c0s';
        $test_cases[$count]['msg'] = 'Password with invalid char ^';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'afscc0s';
        $test_cases[$count]['msg'] = 'Password with not upper case chars';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'afsccAs';
        $test_cases[$count]['msg'] = 'Password with not number chars';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'ALFM5SLE';
        $test_cases[$count]['msg'] = 'Password with not lower case chars';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'asSDsd4ksFmr4fkDA43cz';
        $test_cases[$count]['msg'] = 'Password too long';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $test_cases[$count]['fields']['email'] = ($mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        $test_cases[$count]['fields']['plainPassword'] = 'Test!123';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['msg'] = "Request for user creation";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 201;

        $count++;
        $test_cases[$count]['fields']['email'] = $mail;
        $test_cases[$count]['fields']['plainPassword'] = 'Test!123';
        $test_cases[$count]['fields']['idCalendarTimezone'] = 422;
        $test_cases[$count]['fields']['idClient'] = $clientId;
        $test_cases[$count]['msg'] = "Request for user with already existing email";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testCreateConfirmAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "createConfirmAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/public/user/confirm/{id_user}/{token}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array('id_user' => '', 'token' => '');
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $user = $this->createUser($configs['controller'], $email = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        fwrite(STDOUT, json_encode($user) . "\n. . . .\n");

        $user = $user['results'];

        $count = 0;
        $test_cases[$count]['requirements']['id_user'] = '0';
        $test_cases[$count]['requirements']['token'] = $user['confirm_token'];
        $test_cases[$count]['msg'] = "Invalid id_user";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user['id'];
        $test_cases[$count]['requirements']['token'] = 'x';
        $test_cases[$count]['msg'] = "Invalid token";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user['id'];
        $test_cases[$count]['requirements']['token'] = $user['confirm_token'] . 'x';
        $test_cases[$count]['msg'] = "Wrong token";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user['id'] + 1000;
        $test_cases[$count]['requirements']['token'] = $user['confirm_token'];
        $test_cases[$count]['msg'] = "Not existing id_user";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 404;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user['id'];
        $test_cases[$count]['requirements']['token'] = $user['confirm_token'];
        $test_cases[$count]['msg'] = "Request for enabling user";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user['id'];
        $test_cases[$count]['requirements']['token'] = $user['confirm_token'];
        $test_cases[$count]['msg'] = "Request for user already enabled";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 403;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testResendMailConfirmationAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "resendMailConfirmationAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/public/user/resend/confirm/{email}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array('email' => '');
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $user = $this->createEnabledUser($configs['controller'], $email = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        fwrite(STDOUT, json_encode($user) . "\n. . . .\n");

        $count = 0;
        $test_cases[$count]['requirements']['email'] = 'xxx@xxx.com';
        $test_cases[$count]['msg'] = "Wrong email";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['email'] = $email;
        $test_cases[$count]['msg'] = "Resend request";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $count++;
        $test_cases[$count]['requirements']['email'] = $email;
        $test_cases[$count]['msg'] = "Request for a consecutive resending mail confirmation but send 200";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testLogin($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "login";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/public/oauth/v2/token?grant_type=password&client_id={client_id}&client_secret={client_secret}&username={username}&password={password}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array(
            'client_id' => '',
            'client_secret' => '',
            'username' => '',
            'password' => ''
        );
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $email               = $configs['client']->getKernel()->getContainer()->getParameter('test_private_email');
        $password            = $configs['client']->getKernel()->getContainer()->getParameter('test_private_password');
        $client_secret       = $configs['client']->getKernel()->getContainer()->getParameter('test_client_secret');
        $public_id           = $configs['client']->getKernel()->getContainer()->getParameter('test_public_id');

        $count = 0;
        $test_cases[$count]['requirements']['client_id'] = '';
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Empty client_id";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = 'xxx';
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Wrong client_id";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = '';
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Empty client_secret";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = 'xxx';
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Wrong client_secret";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = '';
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Empty username";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = 'xxx';
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Wrong username";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = '';
        $test_cases[$count]['msg'] = "Empty password";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = 'xxx';
        $test_cases[$count]['msg'] = "Wrong password";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['client_id'] = $public_id;
        $test_cases[$count]['requirements']['client_secret'] = $client_secret;
        $test_cases[$count]['requirements']['username'] = $email;
        $test_cases[$count]['requirements']['password'] = $password;
        $test_cases[$count]['msg'] = "Login";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testResetAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "resetAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/public/user/reset/{email}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array('email' => '');
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $count = 0;
        $test_cases[$count]['requirements']['email'] = 'test';
        $test_cases[$count]['msg'] = "Invalid email";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $user_enabled = $this->createEnabledUser($configs['controller'], $mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        fwrite(STDOUT, json_encode($user_enabled) . "\n. . . .\n");

        $count++;
        $test_cases[$count]['requirements']['email'] = $mail;
        $test_cases[$count]['msg'] = "Request for a password reset";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testResetConfirmAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "resetConfirmAction";

        $configs['header'] = array();
        $configs['method'] = "POST";
        $configs['route'] = "/public/user/reset/confirm/{id_user}/{token}";

        $configs['fields'] = array('new_password' => 'Test!125');
        $configs['requirements'] = array('id_user' => '', 'token' => '');
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $response_login = $this->loginAdmin($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];

        $user = $this->createEnabledUser($configs['controller'], $mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        fwrite(STDOUT, json_encode($user) . "\n. . . .\n");
        $reset_request = $this->resetPasswordRequest($configs['controller'], $mail);

        $user_results = $this->getUser($configs['controller'], $token, $user['results']['id']);
        $user_results = $user_results['results'];
        fwrite(STDOUT, json_encode($reset_request) . "\n. . . .\n");

        $count = 0;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = 'xxx';
        $test_cases[$count]['msg'] = "Invalid confirm token";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'] + 1000;
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['msg'] = "Not existing user_id";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 404;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = '';
        $test_cases[$count]['msg'] = "Blank new password";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'afslces';
        $test_cases[$count]['msg'] = 'Password too short';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'afsD^c0s';
        $test_cases[$count]['msg'] = 'Password with invalid char ^';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'afscc0s';
        $test_cases[$count]['msg'] = 'Password with not upper case chars';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'afsccAs';
        $test_cases[$count]['msg'] = 'Password with not number chars';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'ALFM5SLE';
        $test_cases[$count]['msg'] = 'Password with not lower case chars';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'asSDsd4ksFmr4fkDA43cz';
        $test_cases[$count]['msg'] = 'Password too long';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['requirements']['id_user'] = $user_results['id'];
        $test_cases[$count]['requirements']['token'] = $user_results['confirm_token'];
        $test_cases[$count]['fields']['new_password'] = 'Test!123';
        $test_cases[$count]['msg'] = 'Password reset execution';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testLogout($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "logoutAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/private/user/logout";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array();
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $user_login = $this->loginPrivate($configs['controller'], $configs['client']);
        fwrite(STDOUT, json_encode($user_login) . "\n. . . .\n");

        $count = 0;
        $test_cases[$count]['msg'] = "Logout the user";
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testRefreshAction($call_params = null) {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "refreshAction";

        $configs['header'] = array();
        $configs['method'] = "PUT";
        $configs['route'] = "/private/user/refresh";

        //$configs['fields'] = array('old_password' => $old_password, 'new_password' => $new_password);
        $configs['requirements'] = array();
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        $user_create = $this->createEnabledUser($configs['controller'], $mail = TestingUtils::randomValue('user') . '@fastartconsulting.com');
        fwrite(STDOUT, json_encode($user_create) . "\n. . . .\n");

        $old_password = 'Test!123';
        $new_password = 'Test!125';

        $user_login = $this->login($configs['controller'], $mail, $old_password);
        fwrite(STDOUT, json_encode($user_login) . "\n. . . .\n");

        $count = 0;
        $test_cases[$count]['fields']['old_password'] = '';
        $test_cases[$count]['fields']['new_password'] = $new_password;
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Empty old password';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = '';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Empty new password';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = $old_password;
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'No changing password';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password . "xxx";
        $test_cases[$count]['fields']['new_password'] = "TestPass611";
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Wrong old password';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 403;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = 'afslces';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password too short';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = 'afsD^c0s';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password with invalid char ^';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = 'afscc0s';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password with not upper case chars';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = 'afsccAs';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password with not number chars';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = 'ALFM5SLE';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password with not lower case chars';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = 'asSDsd4ksFmr4fkDA43cz';
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password too long';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['fields']['old_password'] = $old_password;
        $test_cases[$count]['fields']['new_password'] = $new_password;
        $test_cases[$count]['access_token'] = $user_login['access_token'];
        $test_cases[$count]['msg'] = 'Password refresh request';
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testVerifyPasswordAction($call_params = null)
    {
        $this->test_data = array("plainPassword"=>"");

        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "verifyPasswordAction";

        $configs['header'] = array();
        $configs['method'] = "POST";
        $configs['route'] = "/private/user/verify/password";
        $configs['fields'] =  $this->test_data;
        $configs['attachments'] = array();
        $configs['requirements'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        ### START test cases configuration ###

        $response_login = $this->loginPrivate($configs['controller'],$configs['client']);
        $token = $response_login['access_token'];

        ### START test cases configuration ###

        $count = 0;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "The parameters are empty";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "The parameter plainPassword is invalid";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['plainPassword'] = 's2.-<°SS#`§§';

        $count++;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "Success";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;
        $test_cases[$count]['fields']['plainPassword'] = $this->password;

        ### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count + 1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testCheckEmailExistsAction($call_params = null)
    {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "checkEmailExistsAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/public/verify-email/{email}";
        $configs['fields'] =  $this->test_data;
        $configs['attachments'] = array();
        $configs['requirements'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        ### START test cases configuration ###

        $response_login = $this->loginPrivate($configs['controller'],$configs['client']);
        $token = $response_login['access_token'];

        ### START test cases configuration ###

        $count = 0;
        $test_cases[$count]['msg'] = "The parameters are empty";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['msg'] = "The parameter email is invalid";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['requirements']['email'] = 'luca@';

        $count++;
        $test_cases[$count]['msg'] = "Success";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;
        $test_cases[$count]['requirements']['email'] = 'user@fastartconsulting.com';

        ### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count + 1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    ####################################################################

    public function getUser($caller, $token, $id_user) {
        $params['access_token'] = $token;
        $params['id_user'] = $id_user;
        $params['source'] = $caller;
        return $this->testShowAction($params);
    }

    public function createUser($caller, $email) {
        $params['fields']['email'] = $email;
        $params['fields']['plainPassword'] = 'Test!123';
        $params['fields']['idCalendarTimezone'] = 422;

        $staticClient = static::createClient();
        /** @var Client $client */
        $client = $staticClient->getContainer()->get('FAC\UserBundle\Service\ClientService')->getOneByAttributes(array("id"=>"1"));

        $params['fields']['idClient'] = $client->getPublicId();
        $params['source'] = $caller;
        return $this->testCreateAction($params);
    }

    public function createEnabledUser($caller, $email) {
        $create = $this->createUser($caller, $email);
        $create = $create['results'];
        $params['id_user'] = $create['id'];
        $params['token'] = $create['confirm_token'];
        $params['source'] = $caller;
        return $this->testCreateConfirmAction($params);
    }

    public function login($caller, $email, $password) {
        $params['source'] = $caller;
        $params['client_id'] = $this->public_id;
        $params['client_secret'] = $this->client_secret;
        $params['username'] = $email;
        $params['password'] = $password;
        return $this->testLogin($params);
    }

    public function resetPasswordRequest($caller, $email) {
        $params['email'] = $email;
        $params['source'] = $caller;
        return $this->testResetAction($params);
    }

    public function resetPasswordConfirm($caller, $email, $password) {
        $request = $this->resetPasswordRequest($caller, $email);
        $params['token'] = $request['confirm_token'];
        $params['user_id'] = $request['user_id'];
        $params['fields']['new_password'] = $password;
        $params['source'] = $caller;
        return $this->testResetConfirmAction($params);
    }

    public function loginPrivate($caller, $client) {
        $this->setByParameters($client, 'private');
        $params['source'] = $caller;
        $params['client_id'] = $this->public_id;
        $params['client_secret'] = $this->client_secret;
        $params['username'] = $this->email;
        $params['password'] = $this->password;
        return $this->testLogin($params);
    }

    public function loginAdmin($caller, $client) {
        $this->setByParameters($client, 'admin');
        $params['source'] = $caller;
        $params['client_id'] = $this->public_id;
        $params['client_secret'] = $this->client_secret;
        $params['username'] = $this->email;
        $params['password'] = $this->password;
        return $this->testLogin($params);
    }

    public function loginSuper($caller, $client) {
        $this->setByParameters($client, 'super');
        $params['source'] = $caller;
        $params['client_id'] = $this->public_id;
        $params['client_secret'] = $this->client_secret;
        $params['username'] = $this->email;
        $params['password'] = $this->password;
        return $this->testLogin($params);
    }

    public function setByParameters($client, $type) {
        $this->email         = $client->getKernel()->getContainer()->getParameter('test_'.$type.'_email');
        $this->password      = $client->getKernel()->getContainer()->getParameter('test_'.$type.'_password');
        $this->client_secret = $client->getKernel()->getContainer()->getParameter('test_client_secret');
        $this->public_id     = $client->getKernel()->getContainer()->getParameter('test_public_id');
    }

}
