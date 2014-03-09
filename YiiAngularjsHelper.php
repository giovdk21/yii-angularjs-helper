<?php

/**
 * Class YiiAngularjsHelper
 *
 * @author  Giovanni Derks
 * @link    https://github.com/giovdk21/yii-angularjs-helper
 * @version 0.2.1
 *
 * License: MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 *
 * Main features:
 * - publish and load AngularJS base script and required modules
 * - publish and load your AngularJS application assets
 * - concatenate AngularJS application scripts into a single file
 * - replace placeholders values on assets pubblication
 * - flexible configuration
 *
 */
class YiiAngularjsHelper extends CWidget
{

    /** Name of the angular folder inside the vendor folder */
    const ANGULARJS_FOLDER = 'angular-1.2.14';

    /** Default parameter placeholders to be replaed when assets are published */
    const APP_ASSETS_URL_PLACEHOLDER = '[:APP_ASSETS_URL]';
    const APP_NAME_PLACEHOLDER = '_PH__APP_NAME_';
    const THEME_URL_PLACEHOLDER = '[:THEME_URL]';

    /** @var string the name of the AngularJS application */
    public $appName;

    /** @var bool if true, the appName will be appended to the ng-app attribute */
    public $hasAppModule = false;

    /** @var string the path to the folder that contains our application files */
    public $appFolder;

    /** @var string the name of the folder that contains the partial html files */
    public $partialsFolder = 'partials';

    /** @var array the list of scripts to be loaded (relative path from $appFolder) */
    public $appScripts = array();

    /** @var array the list of styles to be loaded (relative path from $appFolder) */
    public $appStyles = array();

    /** @var bool whether to concatenate the application scripts in one single JS file */
    public $concatenateAppScripts = true;

    /** @var string the name of the generated concatenated script */
    public $concatenatedFilename = 'app_scripts.js';

    /** @var array the list of common scripts that are shared among different applications;
     * the _PH__APP_NAME_ placeholder will be replaced with $appName */
    public $commonAppScripts = array();

    /** @var array the list of path and urls of the published common app. scripts */
    private $_publishedCommonAppScripts = array();

    /** @var array list of names of AngularJS modules to be loaded (vendor/angular/angular-<name>.min.js) */
    public $requiredModulesScriptNames = array();

    /** @var array placeholderName => value list of custom placeholders to be replaced when assets are published */
    public $customPlaceholders = array();

    /** @var bool if false the AngularJS scripts won't be included (assuming that you are doing it somewhere else) */
    public $includeAngular = true;

    /** @var bool if true the widget will output a wrapper with the ng-app attribute; set it to false if you
     * put the ng-app attribtue somewhere else in your template */
    public $embedded = true;

    /** @var string where to place the registered scripts (if null CClientScript::POS_END will be used) */
    public $scriptsPosition = null;

    /** @var bool in debug mode the Angular script will be loaded in the non minimised version and assets will be
     *  re-published at every page reload */
    public $debug = null;


    // Assets paths:
    /** @var string url of the published vendor assets */
    private $_vendorAssets;
    /** @var string url of the published application assets */
    private $_appAssetsUrl;
    /** @var string path of the published application assets */
    private $_appAssetsPath;

    /** @var  $_clientScript CClientScript */
    private $_clientScript;

    /**
     * Called when the widget is initialised (widget() or beginWidget())
     *
     * Init the widget:
     *
     * - set default value of scriptsPosition if null
     * - set default value of the debug flag if null
     * - publish assets and register scripts & styles
     * - open the ng-app container if embedded is set to true
     */
    public function init() {

        // set the default value for scriptsPosition to POS_END if not specified
        if ($this->scriptsPosition === null) {
            $this->scriptsPosition = CClientScript::POS_END;
        }

        // set the default value of the debug flag to YII_DEBUG if not specified
        if ($this->debug === null) {
            $this->debug = YII_DEBUG;
        }

        $this->publishAssets();
        $this->registerScripts();

        if ($this->embedded) {
            echo '<div data-ng-app' . ($this->hasAppModule ? '="' . $this->appName . '"' : '') . '>';
        }
    }

    /**
     * Called when the widget is ran (widget() or endWidget())
     *
     * - close the ng-app container if embedded is set to true
     */
    public function run() {

        if ($this->embedded) {
            echo "</div>\n";
        }
    }

    /**
     * Publishes the assets
     */
    public function publishAssets() {

        /** @var CAssetManager $assetsManager */
        $assetsManager = Yii::app()->getAssetManager();

        // publish the vendor assets:
        if ($this->includeAngular) {
            $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . self::ANGULARJS_FOLDER;
            $this->_vendorAssets = $assetsManager->publish($dir);
        }

        // publish the angular application folder, if specified
        if (!empty($this->appFolder)) {

            $this->_appAssetsUrl = $assetsManager->getPublishedUrl($this->appFolder);
            $this->_appAssetsPath = $assetsManager->getPublishedPath($this->appFolder);

            // if the $this->_appAssetsUrl folder does not exists, assets have not been published yet
            // in debug mode we always publish the app assets and do the replacements
            if ($this->debug || !file_exists($this->_appAssetsPath)) {

                // publish the app assets; if debug mode is enable the assets are copied even if already published
                $this->_appAssetsUrl = $assetsManager->publish($this->appFolder, false, -1, $this->debug);

                // do replacements & concatenation:
                if ($this->concatenateAppScripts) {
                    $this->_saveConcatenatedAppScripts();
                }
                else {
                    if ($this->includeAngular) {
                        $this->_publishCommonAppScripts();
                    }
                    $this->_processAppScripts(); // (replacements in non concatenated scripts)
                }

                $this->_processPartials(); // replacements in the html partial files
            }
        }
    }


    /**
     * Register the required css and javascript files
     *
     * @throws CException if any of the assets base urls is not defined
     */
    public function registerScripts() {

        if ($this->_vendorAssets === '') {
            throw new CException('_vendorAssets must be set!');
        }

        $jsExt = ($this->debug ? '.js' : '.min.js');

        $this->_clientScript = Yii::app()->getClientScript();

        // JS
        if ($this->includeAngular) {

            // register the main AngularJS script
            $this->_clientScript->registerScriptFile(
                $this->_vendorAssets . DIRECTORY_SEPARATOR . 'angular' . $jsExt,
                $this->scriptsPosition
            );

            // register the requested additional AngularJS modules
            foreach ($this->requiredModulesScriptNames as $scriptName) {
                $this->_clientScript->registerScriptFile(
                    $this->_vendorAssets . DIRECTORY_SEPARATOR . 'angular-' . $scriptName . $jsExt,
                    $this->scriptsPosition
                );
            }
        }

        // register the application scripts, if available
        if (!empty($this->appFolder)) {

            if ($this->concatenateAppScripts) {
                // register the concatenated script:
                $this->_clientScript->registerScriptFile(
                    $this->_appAssetsUrl . DIRECTORY_SEPARATOR . $this->concatenatedFilename,
                    $this->scriptsPosition
                );
            }
            elseif (!empty($this->_appAssetsUrl) && !empty($this->appScripts)) {

                // register the requested application scripts in the given order:
                foreach ($this->appScripts as $scriptFile) {
                    $this->_clientScript->registerScriptFile(
                        $this->_appAssetsUrl . DIRECTORY_SEPARATOR . $scriptFile,
                        $this->scriptsPosition
                    );
                }

                if ($this->includeAngular) {
                    // register the requested common scripts in the given order:
                    foreach ($this->_publishedCommonAppScripts as $scriptFileUrl) {
                        $this->_clientScript->registerScriptFile(
                            $scriptFileUrl,
                            $this->scriptsPosition
                        );
                    }
                }
            }

            // register the requested application specific CSS files:
            foreach ($this->appStyles as $cssFile) {
                $this->_clientScript->registerCssFile($this->_appAssetsUrl . DIRECTORY_SEPARATOR . $cssFile);
            }
        }

    }

    /**
     * Concatenate the application scripts in a single file and replace the placeholders
     */
    private function _saveConcatenatedAppScripts() {

        $concatenated = '';

        // concatenate the application scripts:
        foreach ($this->appScripts as $scriptFile) {
            $concatenated .= file_get_contents($this->_appAssetsPath . DIRECTORY_SEPARATOR . $scriptFile) . "\n";
        }

        if ($this->includeAngular) {
            // concatenate the common scripts:
            foreach ($this->commonAppScripts as $scriptFile) {
                $concatenated .= file_get_contents($scriptFile) . "\n";
            }
        }

        // replace the placeholders:
        $this->_replacePlaceholders($concatenated);

        // save the new generated file:
        $concatenatedFilePath = $this->_appAssetsPath . DIRECTORY_SEPARATOR . $this->concatenatedFilename;
        file_put_contents($concatenatedFilePath, $concatenated);
        @chmod($concatenatedFilePath, Yii::app()->getAssetManager()->newFileMode);
    }

    /**
     * Publish the common application scripts in the application assets folder and stores the
     * path => url association in the _publishedCommonAppScripts array
     */
    private function _publishCommonAppScripts() {

        foreach ($this->commonAppScripts as $scriptFile) {

            $scriptFileName = basename($scriptFile);

            $commonFileUrl = $this->_appAssetsUrl . DIRECTORY_SEPARATOR . $scriptFileName;
            $commonFilePath = $this->_appAssetsPath . DIRECTORY_SEPARATOR . $scriptFileName;

            $this->_publishedCommonAppScripts[$commonFilePath] = $commonFileUrl;
            copy($scriptFile, $commonFilePath);
            @chmod($commonFilePath, Yii::app()->getAssetManager()->newFileMode);
        }
    }

    /**
     * Process each application script and the common scripts;
     * this method is called when scripts are not being concatenated (!concatenateAppScripts)
     */
    private function _processAppScripts() {

        foreach ($this->appScripts as $scriptFile) {
            $fileName = $this->_appAssetsPath . DIRECTORY_SEPARATOR . $scriptFile;
            $this->_processFile($fileName);
        }

        if ($this->includeAngular) {
            foreach ($this->_publishedCommonAppScripts as $scriptFilePath => $scriptFileUrl) {
                $this->_processFile($scriptFilePath);
            }
        }
    }

    /**
     * Process the partials html files replacing theirs placeholders
     */
    private function _processPartials() {

        $partialsPath = $this->_appAssetsPath . DIRECTORY_SEPARATOR . $this->partialsFolder;

        $partials = glob($partialsPath . DIRECTORY_SEPARATOR . '*.html');

        foreach ($partials as $fileName) {
            $this->_processFile($fileName);
        }
    }

    /**
     * Replace placeholders in the given fileName and save the file
     *
     * @param string $fileName
     */
    private function _processFile($fileName) {

        $content = file_get_contents($fileName);
        $this->_replacePlaceholders($content);
        file_put_contents($fileName, $content);
    }

    /**
     * Replace the default and custom placeholders found in the given content
     *
     * @param string $content the content that contains the placeholders to be replaced (by reference)
     */
    private function _replacePlaceholders(& $content) {

        $content = str_replace(self::APP_ASSETS_URL_PLACEHOLDER, $this->_appAssetsUrl, $content);
        $content = str_replace(self::APP_NAME_PLACEHOLDER, $this->appName, $content);
        if (!empty(Yii::app()->theme)) {
            $content = str_replace(self::THEME_URL_PLACEHOLDER, Yii::app()->theme->baseUrl, $content);
        }

        foreach ($this->customPlaceholders as $ph => $val) {
            $content = str_replace($ph, $val, $content);
        }
    }

}
