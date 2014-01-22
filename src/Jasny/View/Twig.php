<?php

namespace Jasny\View;

use Jasny\View;

/**
 * View using Twig
 */
class Twig extends View
{
    /** @var \Twig_Template */
    protected $template;
    
    
    /** @var \Twig_Environment */
    protected static $environment;
    
    
    /**
     * Class constructor
     * 
     * @param string $name  Template filename
     */
    public function __construct($name)
    {
        if (!pathinfo($name, PATHINFO_EXTENSION)) $name .= '.html.twig';
        $this->template = self::getEnvironment()->loadTemplate($name);
    }

    /**
     * Render the template
     * 
     * @param array $context
     * @return string
     */
    public function render($context)
    {
        return $this->template->render($context);
    }
    
    /**
     * Display the template
     * 
     * @param array $context
     * @return string
     */
    public function display($context)
    {
        header('Content-type: text/html; charset=utf-8');
        $this->template->display($context);
    }

    /**
     * Add a global variable to the view.
     * 
     * @param string $name   Variable name
     * @param mixed  $value
     * @return Twig $this
     */
    public function set($name, $value)
    {
        static::getEnvironment()->addGlobal($name, $value);
        return $this;
    }
    
    
    /**
     * Init Twig environment.
     * Initializing automatically sets Twig to be used by default.
     * 
     * @param string|array $path   Path to the templates 
     * @param string       $cache  The cache directory or false if cache is disabled.
     * @return \Twig_Environment
     */
    public static function init($path=null, $cache=false)
    {
        if (View::using() == null) View::using('twig');
        
        if (!isset($path)) $path = getcwd();
        
        $loader = new \Twig_Loader_Filesystem($path);

        // Set options like caching or debug http://twig.sensiolabs.org/doc/api.html#environment-options
        $twig = new \Twig_Environment($loader);
        $twig->setCache($cache);
        
        // Add filters and extensions http://twig.sensiolabs.org/doc/api.html#using-extensions
        $twig->addFunction(new \Twig_SimpleFunction('useFlash', [__CLASS__, 'useFlash']));
        
        if (class_exists('Jasny\Twig\DateExtension')) $twig->addExtension(new \Jasny\Twig\DateExtension());
        if (class_exists('Jasny\Twig\PcreExtension')) $twig->addExtension(new \Jasny\Twig\PcreExtension());
        if (class_exists('Jasny\Twig\TextExtension')) $twig->addExtension(new \Jasny\Twig\TextExtension());
        if (class_exists('Jasny\Twig\ArrayExtension')) $twig->addExtension(new \Jasny\Twig\ArrayExtension());
        
        // Set globals http://twig.sensiolabs.org/doc/advanced.html#globals
        $twig->addGlobal('current_url', rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        
        self::$environment = $twig;
        return self::$environment;
    }

    /**
     * Get Twig environment
     * 
     * @return \Twig_Environment
     */
    public static function getEnvironment()
    {
        if (!isset(static::$environment)) static::init();
        return static::$environment;
    }
}
