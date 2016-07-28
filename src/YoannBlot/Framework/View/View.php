<?php

namespace YoannBlot\Framework\View;

use YoannBlot\Framework\Controller\AbstractController;
use YoannBlot\Framework\Controller\Exception\Redirect404Exception;
use YoannBlot\Framework\Utils\File\Directory;
use YoannBlot\Framework\Utils\File\Loader;
use YoannBlot\Framework\Utils\Log\Log;
use YoannBlot\Framework\Validator\Boolean;

/**
 * Class View
 *
 * @package YoannBlot\Framework\View
 */
class View {

    /**
     * View directory name.
     */
    const VIEW_DIR_NAME = 'View';

    /**
     * Template extension file.
     */
    const TEMPLATE_EXT = '.php';

    /**
     * Template file.
     */
    const TEMPLATE = 'template';

    /**
     * @var AbstractController controller.
     */
    private $oControllerClass;

    /**
     * @var array parameters.
     */
    private $aParameters;

    /**
     * @var string cache path.
     */
    private $sCachePath = '';

    /**
     * View constructor.
     *
     * @param AbstractController $oControllerClass
     * @param array              $aParameters view parameters
     */
    public function __construct (AbstractController $oControllerClass, array $aParameters) {
        $this->oControllerClass = $oControllerClass;
        $this->aParameters = $aParameters;
    }

    /**
     * @return string cleaned current class name.
     */
    private function getCurrentClassName () : string {
        $sClassName = get_class($this->oControllerClass);
        $sClassName = substr($sClassName, strrpos($sClassName, '\\') + 1);
        $sClassName = substr($sClassName, 0, strpos($sClassName, 'Controller'));

        return $sClassName;
    }

    /**
     * @return string view directory.
     */
    private function getViewRootDirectory () : string {
        $sDirectory = '';

        $sProjectPath = get_class($this->oControllerClass);
        $sProjectPath = substr($sProjectPath, 0, strrpos($sProjectPath, '\\Controller'));
        $sProjectPath = str_replace('\\', DIRECTORY_SEPARATOR, $sProjectPath);

        $sDirectory .= SRC_PATH;
        $sDirectory .= $sProjectPath . DIRECTORY_SEPARATOR;

        $sDirectory .= static::VIEW_DIR_NAME . DIRECTORY_SEPARATOR;

        return $sDirectory;
    }

    /**
     * Get right template file.
     * First check in View/[Controller]/[page]/template.php
     * If not found, check in View/[Controller]/template.php
     * Last chance is : View/template.php
     *
     * @return string template file
     */
    private function getTemplate () : string {
        $sTemplate = $this->getViewPath() . static::TEMPLATE . static::TEMPLATE_EXT;
        if (!is_file($sTemplate)) {
            $sTemplate = $this->getViewRootDirectory();
            $sTemplate .= $this->getCurrentClassName() . DIRECTORY_SEPARATOR;
            $sTemplate .= static::TEMPLATE . static::TEMPLATE_EXT;
            if (!is_file($sTemplate)) {
                $sTemplate = $this->getViewRootDirectory();
                $sTemplate .= static::TEMPLATE . static::TEMPLATE_EXT;
            }
        }

        return $sTemplate;
    }

    /**
     * @return string View directory
     */
    private function getViewPath () : string {
        $sDirectory = $this->getViewRootDirectory();
        $sDirectory .= $this->getCurrentClassName() . DIRECTORY_SEPARATOR;
        $sDirectory .= $this->oControllerClass->getCurrentPage() . DIRECTORY_SEPARATOR;

        return $sDirectory;
    }

    /**
     * @return string template cache path.
     */
    private function getCachePath (): string {
        if ('' === $this->sCachePath) {
            $this->sCachePath = '';
            $this->sCachePath .= ROOT_PATH . 'var' . DIRECTORY_SEPARATOR;
            $this->sCachePath .= 'cache' . DIRECTORY_SEPARATOR;
            $this->sCachePath .= 'View' . DIRECTORY_SEPARATOR;
            $this->sCachePath .= $this->getCurrentClassName() . DIRECTORY_SEPARATOR;
            $this->sCachePath .= $this->oControllerClass->getCurrentPage() . DIRECTORY_SEPARATOR;
            $this->sCachePath .= 'template.cache' . static::TEMPLATE_EXT;

            Directory::create(dirname($this->sCachePath));
        }

        return $this->sCachePath;
    }

    /**
     * @return bool true if need cache construction, otherwise false.
     */
    private function needCache (): bool {
        $bDebugMode = Boolean::getValue(Loader::get('debug'));
        $bNeedCache = $bDebugMode || !is_file($this->getCachePath());

        return $bNeedCache;
    }

    /**
     * Generate template cache.
     */
    private function generateCache () {
        $sTemplateContent = file_get_contents($this->getTemplate());

        preg_match_all('#\[\[([a-z -]+)\]\]#', $sTemplateContent, $aBlocks);
        if (count($aBlocks[1]) > 0) {
            foreach ($aBlocks[1] as &$sTemplateBlock) {
                $sTemplateBlock = $this->getViewPath() . trim($sTemplateBlock) . static::TEMPLATE_EXT;
                if (is_file($sTemplateBlock)) {
                    $sTemplateBlock = file_get_contents($sTemplateBlock);
                }
            }
        }
        $sTemplateContent = str_replace($aBlocks[0], $aBlocks[1], $sTemplateContent);

        file_put_contents($this->getCachePath(), $sTemplateContent);

        $this->copyResources();
    }

    /**
     * Copy all necesary resources.
     */
    private function copyResources () {
        $sResourcesDirectory = $this->getViewRootDirectory();
        $sResourcesDirectory = substr($sResourcesDirectory, 0, strrpos($sResourcesDirectory, 'View'));
        $sResourcesDirectory .= 'Resources' . DIRECTORY_SEPARATOR;

        // copy css
        foreach (glob($sResourcesDirectory . 'css/*') as $sResourceFile) {
            $sCopyPath = WWW_PATH . substr($sResourceFile, strlen($sResourcesDirectory));
            Directory::create(dirname($sCopyPath));
            copy($sResourceFile, $sCopyPath);
        }

        // copy js
        foreach (glob($sResourcesDirectory . 'js/*') as $sResourceFile) {
            $sCopyPath = WWW_PATH . substr($sResourceFile, strlen($sResourcesDirectory));
            Directory::create(dirname($sCopyPath));
            copy($sResourceFile, $sCopyPath);
        }
    }

    /**
     * Display current view.
     *
     * @throws Redirect404Exception
     */
    public function display () {
        $sTemplateFile = $this->getTemplate();

        if (!is_file($sTemplateFile)) {
            Log::get()->warn("View template '$sTemplateFile' not found. Please ensure file exists.");
            throw new Redirect404Exception();
        } else {
            if ($this->needCache()) {
                $this->generateCache();
            }
            extract($this->aParameters);
            /** @noinspection PhpIncludeInspection */
            require $this->getCachePath();
        }
    }
}