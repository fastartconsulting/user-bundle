<?php
namespace FAC\UserBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class TestingUtils
{

    public static function getHttpAuthorization($access_token) {
        return 'Bearer ' . $access_token;
    }

    public static function getRequestRoute($route, array $requirements) {
        if(count($requirements)>0) {
            foreach($requirements as $requirement => $value) {
                if(!is_array($value)) {
                    $route = str_replace("{".$requirement."}",$value,$route);
                }
            }
        }

        return $route;
    }

    public static function getRequestBody(array $data) {
        if(is_null($data))
            return null;

        if(count($data)<=0)
            return null;

        return json_encode($data);
    }

    public static function writeLog($msg, $bundle, $controller, $function, $source = null, $check_count = 0, $check_total = 0) {
        $now = strftime('%Y-%m-%d %H:%M:%S');

        $testPath = __DIR__ . '/../../../../tests/';

        if(!file_exists($testPath.'Logs')) {
            mkdir($testPath.'Logs');
        }

        $logPath = $testPath.'/Logs/';

        if(!file_exists($logPath.$bundle)) {
            mkdir($logPath.$bundle);
        }

        if(!is_null($source)) {
            $log_file = $logPath.$bundle.'/'.$source.".log";
            $handle = fopen($log_file, "a");
            fwrite(STDOUT, "CALL => $controller::$function $msg\n");
            fwrite($handle, "\n$now - CALL => $controller::$function $msg\n\n");
        } else {
            $log_file = $logPath.$bundle.'/'.$controller.".log";
            $handle = fopen($log_file, "a");
            fwrite(STDOUT, "$controller::$function\t\t=> CHECK\t$check_count/$check_total\t- $msg\n");
            fwrite($handle, "$now - $function\t\t=> CHECK\t$check_count/$check_total\t- $msg\n");
        }

        fclose($handle);
    }

    public static function executionTestCases($configs) {
        $response = array();

        if (isset($configs['cases']) && is_array($configs['cases']) && count($configs['cases']) > 0) {
            foreach ($configs['cases'] as $counter => $params) {
                $bk_configs = $configs;

                if ($params['type'] == 'request') {

                    unset($configs['header']['HTTP_AUTHORIZATION']);
                    if (isset($params['access_token'])) {
                        $configs['header']['HTTP_AUTHORIZATION'] = TestingUtils::getHttpAuthorization($params['access_token']);
                    }

                    if (isset($params['requirements']) && is_array($params['requirements']) && count($params['requirements'])>0) {
                        foreach ($params['requirements'] as $key => $value) {
                            $configs['requirements'][$key] = $value;
                        }
                    }

                    if (isset($params['fields']) && is_array($params['fields']) && count($params['fields'])>0) {
                        foreach ($params['fields'] as $key => $value) {
                            $configs['fields'][$key] = $value;
                        }
                    }

                    unset($configs['header']['CONTENT_TYPE']);
                    if (isset($params['files'])) {
                        $configs['header']['CONTENT_TYPE'] = 'multipart/form-data';
                        $configs['attachments'] = $params['files'];
                    }

                    $usage_before = array();
                    if (isset($params['call']) && is_array($params['call']) && count($params['call'])>0) {
                        foreach ($params['call'] as $call) {
                            if($call['when'] == 'before') {
                                $func = $call['func'];
                                $call_return = $configs['tester']->$func($configs['controller']);
                                if(isset($call['returns']) && is_array($call['returns']) && count($call['returns'])>0) {
                                    foreach ($call['returns'] as $c=>$n) {
                                        $val = $call_return[$n];
                                        $i = 0;
                                        if(count($configs['requirements']) > 0) {
                                            foreach ($configs['requirements'] as $req=>$act_val) {
                                                if($i == $c) {
                                                    $usage_before[$req] = $val;
                                                    break;
                                                }
                                                $i++;
                                            }
                                        } else {
                                            $usage_before[$n] = $val;
                                        }

                                    }
                                }
                            }
                        }
                        if(count($usage_before)>0) {
                            foreach($usage_before as $k=>$v) {
                                if(isset($configs['requirements'][$k]))
                                    $configs['requirements'][$k] = $v;
                            }
                        }
                    }

                    $data = ($configs['method'] == 'POST' || $configs['method'] == 'PUT' || $configs['method'] == 'DELETE' ? TestingUtils::getRequestBody($configs['fields']) : null);

                    $configs['client']->request(
                        $configs['method'],
                        TestingUtils::getRequestRoute($configs['route'], $configs['requirements']),
                        array(),
                        $configs['attachments'],
                        $configs['header'],
                        $data);

                    $configs['tester']->assertSame(
                        $params['return'],
                        $configs['client']->getResponse()->getStatusCode(),
                        $configs['controller'] .
                        "::" . $configs['function'] .
                        " ERROR (check " . ($counter+1) .
                        "/" . $configs['checks'] . ") - " .
                        $params['msg'] . "\n\n" . $configs['client']->getResponse()->getContent() .
                        "\n----\n");


                    $params['msg'] .= " ::: " . trim($configs['client']->getResponse()->getContent());
                    $response = json_decode($configs['client']->getResponse()->getContent(), true);

                    TestingUtils::writeLog($params['msg'], $configs['bundle'], $configs['controller'], $configs['function'], null, $counter+1, $configs['checks']);

                    $usage_after = array();
                    if (isset($params['call']) && is_array($params['call']) && count($params['call'])>0) {
                        foreach ($params['call'] as $call) {
                            if($call['when'] == 'after') {
                                $func = $call['func'];
                                if(isset($call['params']) && is_array($call['params']) && count($call['params'])>0) {

                                    foreach ($call['params'] as $c=>$n) {
                                        if(isset($response[$n])) {
                                            $usage_after[] = $response[$n];
                                        }
                                    }

                                    if(count($call['params'])>0 && count($usage_after)<=0 && count($usage_before)>0) {
                                        foreach ($usage_before as $v) {
                                            $usage_after[] = $v;
                                        }
                                    }

                                }

                                switch(count($usage_after)) {
                                    case 0:
                                        $configs['tester']->$func($configs['controller']);
                                        break;
                                    case 1:
                                        $configs['tester']->$func($configs['controller'], $usage_after[0]);
                                        break;
                                    case 2:
                                        $configs['tester']->$func($configs['controller'], $usage_after[0], $usage_after[1]);
                                        break;
                                    case 3:
                                        $configs['tester']->$func($configs['controller'], $usage_after[0], $usage_after[1], $usage_after[2]);
                                        break;
                                    case 4:
                                        $configs['tester']->$func($configs['controller'], $usage_after[0], $usage_after[1], $usage_after[2], $usage_after[3]);
                                        break;
                                    case 5:
                                        $configs['tester']->$func($configs['controller'], $usage_after[0], $usage_after[1], $usage_after[2], $usage_after[3], $usage_after[4]);
                                        break;
                                }
                            }
                        }
                    }
                }

                if ($params['type'] == 'check') {
                    foreach ($params['check'] as $key => $value) {

                        if (is_bool($value)) {
                            if($value)
                                $configs['tester']->assertTrue($response[$key], $configs['controller'] . "::" . $configs['function'] . " ERROR (check ".($counter+1)."/".$configs['checks'].") - " . $params['msg'] . "\n\n" . $configs['client']->getResponse()->getContent() . "\n----\n");
                            else
                                $configs['tester']->assertFalse($response[$key], $configs['controller'] . "::" . $configs['function'] . " ERROR (check ".($counter+1)."/".$configs['checks'].") - " . $params['msg'] . "\n\n" . $configs['client']->getResponse()->getContent() . "\n----\n");
                        } elseif (is_string($value)) {
                            $configs['tester']->assertSame($value, $response[$key], $configs['controller'] . "::" . $configs['function'] . " ERROR (check ".($counter+1)."/".$configs['checks'].") - " . $params['msg'] . "\n\n" . $configs['client']->getResponse()->getContent() . "\n----\n");
                        } elseif (is_numeric($value)) {
                            $configs['tester']->assertEquals($value, $response[$key], $configs['controller'] . "::" . $configs['function'] . " ERROR (check ".($counter+1)."/".$configs['checks'].") - " . $params['msg'] . "\n\n" . $configs['client']->getResponse()->getContent() . "\n----\n");
                        }

                    }

                    TestingUtils::writeLog($params['msg'], $configs['bundle'], $configs['controller'], $configs['function'], null, $counter+1, $configs['checks']);

                }

                $configs = $bk_configs;
            }
        }
    }

    /**
     * Runs a command and returns it output
     * @param Client $client
     * @param $command
     * @return array
     * @throws \Exception
     */
    public static function runCommand(Client $client, $command) {
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $fp = tmpfile();
        $input = new StringInput($command);
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        fseek($fp, 0);
        $output = '';
        while (!feof($fp)) {
            $output = fread($fp, 4096);
        }
        fclose($fp);

        if(!is_null($output)) {
            $output = explode(PHP_EOL,$output);
        }

        return $output;
    }

    public static function executionTestCommand($configs) {
        $response = array();

        $tester = $configs['tester'];

        if(isset($configs['command'])) {

            if (isset($configs['cases']) && is_array($configs['cases']) && count($configs['cases']) > 0) {
                foreach ($configs['cases'] as $counter => $params) {

                    if (isset($params['output'])) {
                        $tester->assertContains($params['output'], $configs['output']);
                    }

                    if (isset($params['equals'])) {
                        if (isset($params['equals']['compare']) && isset($params['equals']['value'])) {
                            $tester->assertEquals($params['equals']['compare'], $params['equals']['value']);
                        }
                    }
                }
            }


        }

        TestingUtils::writeLog(implode(" - ", $configs['output']), $configs['bundle'], $configs['command_name'], $configs['command_name'], null);
    }

    public static function initTest(string $bundle, string $controller, string $function) {
        sleep(1);
        ini_set('memory_limit', '1024M');
        fwrite(STDOUT, "\n\n\n. . . =========== . . .".$bundle."::".$controller."::".$function.". . . =========== . . .\n");
        $now = strftime('%Y-%m-%d %H:%M:%S');

        $testPath = __DIR__ . '/../../../../tests/';

        if(!file_exists($testPath.'Logs')) {
            mkdir($testPath.'Logs');
        }

        $logPath = $testPath.'/Logs/';

        if(!file_exists($logPath.$bundle)) {
            mkdir($logPath.$bundle);
        }

        $log_file = __DIR__ . '/../../../../tests/Logs/'.$bundle.'/'.$controller.".log";
        $handle = fopen($log_file, "a");
        fwrite($handle, "\n\n\n. . . ========================================== . . .\n$now - $bundle::$controller::$function\n. . . ========================================== . . .\n");
        fclose($handle);
    }

    public static function randomValue(string $seed) {
        return md5(time().random_int(0,1000).$seed);
    }

    public static function executionCallCase($configs, $params, $assertCode=201) {
        if (!is_null($params)) {

            // authorization token (if it is required)
            if (isset($params['access_token'])) {
                $configs['header']['HTTP_AUTHORIZATION'] = TestingUtils::getHttpAuthorization($params['access_token']);
            }


            $configs['requirements'] = $params;
            self::getRequirements($params,$configs);


            // form fields formatting (if PUT or POST request)
            $data = null;
            if (isset($params['fields'])) {
                $data = TestingUtils::getRequestBody($params['fields']);
            }

            // attachments to include (if required)
            $attachments = array();
            if (isset($params['files'])) {
                $configs['header']['CONTENT_TYPE'] = 'multipart/form-data';
                $attachments = $params['files'];
            }

            $configs['client']->request(
                $configs['method'],
                TestingUtils::getRequestRoute($configs['route'], $configs['requirements']),
                array(),
                $attachments,
                $configs['header'],
                $data);

            $configs['tester']->assertSame(
                ($configs['method'] == 'POST' ? $assertCode : 200),
                $configs['client']->getResponse()->getStatusCode(),
                "CALL ERROR => " .
                $configs['controller'] . "::" . $configs['function'] . "\n" .
                $configs['client']->getResponse()->getContent() . "\n");

            $response = json_decode($configs['client']->getResponse()->getContent(), true);


            TestingUtils::writeLog("::: ".json_encode($response), $configs['bundle'], $configs['controller'], $configs['function'], $params['source']);

            return $response;
        }

        return null;
    }


    private static function getRequirements(&$params, &$configs){

        if (isset($params['requirements']) && is_array($params['requirements']) && count($params['requirements'])>0) {
            foreach ($params['requirements'] as $key => $value) {
                $configs['requirements'][$key] = $value;
            }
        }
        $usage_before = array();
        if (isset($params['call']) && is_array($params['call']) && count($params['call'])>0) {
            foreach ($params['call'] as $call) {
                if($call['when'] == 'before') {
                    $func = $call['func'];
                    $call_return = $configs['tester']->$func($configs['controller']);
                    if(isset($call['returns']) && is_array($call['returns']) && count($call['returns'])>0) {
                        foreach ($call['returns'] as $c=>$n) {
                            $val = $call_return[$n];
                            $i = 0;
                            if(count($configs['requirements']) > 0) {
                                foreach ($configs['requirements'] as $req=>$act_val) {
                                    if($i == $c) {
                                        $usage_before[$req] = $val;
                                        break;
                                    }
                                    $i++;
                                }
                            } else {
                                $usage_before[$n] = $val;
                            }

                        }
                    }
                }
            }
            if(count($usage_before)>0) {
                foreach($usage_before as $k=>$v) {
                    if(isset($configs['requirements'][$k]))
                        $configs['requirements'][$k] = $v;
                }
            }
        }
    }

    static public function getDownloadFilePath ($url, $filePath) {
        $randomName = StringUtils::randomString(16);
        $filePath = $filePath.$randomName;

        $fp = fopen($filePath, 'w');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        $data = curl_exec($ch);

        curl_close($ch);
        fclose($fp);

        return $filePath;
    }
}