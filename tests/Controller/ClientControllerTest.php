<?php

namespace FAC\UserBundle\tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use FAC\UserBundle\tests\Controller\UserControllerTest;
use FAC\UserBundle\Utils\TestingUtils;

class ClientControllerTest extends WebTestCase {

    private $bundle     = "UserBundle";
    private $controller = "ClientController";

    private $test_data  = array(
        'keyword'           => '',
        'allowedGrantTypes' => array(),
        'redirectUris'      => array(),
    );


    public function testShowAction($call_params = null){

        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "showAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/admin/client/{id}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array('id' => '');
        $configs['attachments'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        ### START test cases configuration ###
        $userControllerTest = new UserControllerTest();

        $response_login = $userControllerTest->loginAdmin($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];

        $count = 0;
        $id    = "xxx";
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Get by id not numeric check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $id = 0;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Get by id not valid check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $id = "1000000";
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Get by id not existing check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 404;

        $count++;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Get by id check";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        ### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testListAction($call_params = null) {

        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "listAction";

        $configs['header'] = array();
        $configs['method'] = "GET";
        $configs['route'] = "/admin/client/list";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array();
        $configs['attachments'] = array();


        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        //### START test cases configuration ###
        $userControllerTest = new UserControllerTest();

        $response_login = $userControllerTest->loginAdmin($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];


        //### START test cases configuration ###

        $count = 0;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "Get by client list";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;

        //### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }

    public function testCreateAction($call_params = null)
    {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "createAction";

        $configs['header'] = array();
        $configs['method'] = "POST";
        $configs['route'] = "/super/client";
        $configs['fields'] =  $this->test_data;
        $configs['attachments'] = array();
        $configs['requirements'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        ### START test cases configuration ###
        $userControllerTest = new UserControllerTest();

        $response_login = $userControllerTest->loginSuper($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];

        ### START test cases configuration ###

        $count = 0;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "The parameters are empty";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "The parameter keyword is too short";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = 's';

        $count++;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "The parameter keyword is invalid";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = '°°°SS#`§§§';

        $count++;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "The parameter keyword is too long";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
                "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

        $count++;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['msg'] = "Success";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 201;
        $test_cases[$count]['fields']['keyword'] = 'Test';

        ### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count + 1;
        TestingUtils::executionTestCases($configs);

        return true;
    }


    public function testUpdateAction($call_params = null)
    {
        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "updateAction";

        $configs['header'] = array();
        $configs['method'] = "PUT";
        $configs['route'] = "/super/client/{id}";
        $configs['fields'] =  $this->test_data;
        $configs['attachments'] = array();
        $configs['requirements'] = array();

        $response = TestingUtils::executionCallCase($configs, $call_params);
        if (!is_null($response)) return $response;

        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        ### START test cases configuration ###
        $userControllerTest = new UserControllerTest();

        $response_login = $userControllerTest->loginSuper($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];

        ### START test cases configuration ###

        $count = 0;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "The parameters are empty";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;

        $count++;
        $id = 0;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "The parameter id must be > 0";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = 'test';

        $count++;
        $id = 100000;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Not found";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 404;
        $test_cases[$count]['fields']['keyword'] = 'test';

        $count++;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "The parameter keyword is too short";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = 's';

        $count++;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "The parameter keyword is invalid";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = '°°°SS#`§§§';

        $count++;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "The parameter keyword is too long";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 400;
        $test_cases[$count]['fields']['keyword'] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx".
            "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"."xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

        $count++;
        $id = 1;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Success";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;
        $test_cases[$count]['fields']['keyword'] = 'Test';
        $test_cases[$count]['fields']['allowedGrantTypes'] = array("authorization_code","password");
        $test_cases[$count]['fields']['redirectUris'] = array("http:\\meedox.com","http:\\127.0.0.1:8000");

        ### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count + 1;
        TestingUtils::executionTestCases($configs);

        return true;
    }


    public function testDeleteAction($call_params = null) {

        $configs = array();
        $configs['tester'] = $this;
        $configs['client'] = static::createClient();

        $configs['bundle'] = $this->bundle;
        $configs['controller'] = $this->controller;
        $configs['function'] = "deleteAction";

        $configs['header'] = array();
        $configs['method'] = "DELETE";
        $configs['route'] = "/super/client/{id}";

        $configs['fields'] = $this->test_data;
        $configs['requirements'] = array();
        $configs['attachments'] = array();


        TestingUtils::initTest($configs['bundle'], $configs['controller'], $configs['function']);

        //### START test cases configuration ###
        $userControllerTest = new UserControllerTest();

        $response_login = $userControllerTest->loginSuper($configs['controller'], $configs['client']);
        $token = $response_login['access_token'];


        //### START test cases configuration ###

        $count = 0;
        $id = 100000000;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Not found";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 404;

        $count++;
        $id = 2;
        $test_cases[$count]['access_token'] = $token;
        $test_cases[$count]['requirements']['id'] = $id;
        $test_cases[$count]['msg'] = "Success delete";
        $test_cases[$count]['type'] = 'request';
        $test_cases[$count]['return'] = 200;



        //### STOP test cases configuration ###

        $configs['cases'] = $test_cases;
        $configs['checks'] = $count+1;
        TestingUtils::executionTestCases($configs);

        return true;
    }


    ////////////////////////////////////////////////////////////////////

    public function getShowAction($caller, $id){
        return $this->testShowAction(array('source' => $caller, 'requirements' => array('id' => $id)));
    }

}
