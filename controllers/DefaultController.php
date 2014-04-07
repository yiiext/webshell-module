<?php
namespace app\modules\webshell\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\JqueryAsset;
use yii\console;

class DefaultController extends Controller {
    public $layout='webshell';

    function actionError(){
        echo "Error.";
    }

    function actionLogin(){

    }

    function actionIndex(){
        $this->registerAssets();

        $commands = Yii::$app->getModule("webshell")->commands;
        $commandsConfig = array();
        foreach($commands as $name => $command){
            if(is_array($command[0])){
                if(isset($command[0]['DISPATCH'])){
                    $command[0]['DISPATCH'] = $this->normalizeUrl($command[0]['DISPATCH']);

                    if(isset($command[0]['START_HOOK']))
                        $command[0]['START_HOOK'] = $this->normalizeUrl($command[0]['START_HOOK']);

                    if(isset($command[0]['EXIT_HOOK']))
                        $command[0]['EXIT_HOOK'] = $this->normalizeUrl($command[0]['EXIT_HOOK']);
                }
                else {
                    $command[0] = $this->normalizeUrl($command[0]);
                }
            }


            $commandsConfig[$name] = $command[0];
        }

        $config = array(
            'wtermOptions' => Yii::$app->getModule("webshell")->wtermOptions,
            'commands' => $commandsConfig,
            'helpText' => $this->getHelpText(),
            'exitUrl' => Url::toRoute(Yii::$app->getModule("webshell")->exitUrl),
        );

        $this->view->registerJs('var webshell = '.Json::encode($config).';', yii\web\View::POS_HEAD);

	return $this->render('index');
    }

    protected function normalizeUrl($url){
        if(is_array($url))
            return Url::toRoute($url[0]);

        return $url;
    }

    /**
     * Yiic proxy action
     *
     * @return void
     */
    function actionYiic(){
        $tokens = explode(" ", $_GET['tokens']);
        $config = require(__DIR__ . '/../../../config/console.php');

        define('STDOUT',fopen('php://stdout', 'r'));
        
        $app = new \yii\console\Application($config);
        $app->init();
                             
        ob_start();
        
        if(count($tokens) > 1){
            $app->runAction($tokens[1],array_slice($tokens,1));
        } else {
            $app->runAction("",array());
        }

        echo htmlentities(ob_get_clean(), null, Yii::$app->charset);
    }

    /**
     * Forms message for a 'help' command
     * @return string
     */
    protected function getHelpText(){
        $out = array();
        $commands = Yii::$app->getModule("webshell")->commands;
        foreach($commands as $name => $command){
            $out[] = $name."\t".$command[1];
        }
        $out[] = "clear\tClear screen.";
        $out[] = "exit\tExit console.";
        return implode("\n", $out);
    }

    /**
     * Registers required assets
     * @return void
     */
    private function registerAssets(){
        $this->view->registerCssFile(
            Yii::$app->assetManager->publish('@webshell/assets/wterm.css')[1]
	);
        
        $this->view->registerJsFile(
            Yii::$app->assetManager->publish('@webshell/assets/wterm.jquery.js')[1],
            JqueryAsset::className()
        );

        $this->view->registerJsFile(
            Yii::$app->assetManager->publish('@webshell/assets/webshell.js')[1],
            JqueryAsset::className()
        );
    }
}
