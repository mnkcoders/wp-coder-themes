<?php namespace CODERS\Themes;

defined('ABSPATH') or die;

/*******************************************************************************
 * Plugin Name: Coder Themes - Refactor1
 * Plugin URI: https://coderstheme.org
 * Description: Theme Helper Prototype
 * Version: 0.2.1
 * Author: Coder01
 * Author URI: 
 * License: GPLv2 or later
 * Text Domain: coder_themes
 * Domain Path: lang
 * Class: CoderThemes
 ******************************************************************************/

// Just call do_action( 'coder_theme' , get_template_directory() ) and you are done :D
add_action( 'coder_theme', function( $theme_path ){
    if(!is_admin()){
        \CODERS\Themes\CoderThemes::run($theme_path);
    }
});

/**
 * 
 */
abstract class CoderThemes{
    /**
     * @param string $uri
     * @return \CoderThemes
     */
    public static function instance( $uri = '' ){
        return is_null( self::$_instance ) && strlen($uri) ? self::create($uri) : self::$_instance;
    }
    /**
     * @param string $path
     * @return bool
     */
    public static function run( $path = '' ) {
        
        if(strlen($path) && file_exists($path)){
            Theme::create($path)->render();
        }
        return false;
    }
}


/**
 * Create extended content blocks to render each content
 */
class Element{
    
    private $_name =  '';
    private $_class = '';
    
    /**
     * @param string $name
     * @param string $class
     * @param string $wrap
     */
    public function __construct($name = '' , $class = '' ) {
        $this->_name = $name;
        $this->_class = $class;
    }
    
    /**
     * @return string
     */
    public function __toString() {
        return sprintf('%s#%s',$this->type(),$this->name());
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get( $name ){
        return sprintf('<!-- %s -->',$name);
    }
    
    /**
     * @param string $TAG
     * @param array $attributes
     * @param mixed $content
     * @return String|HTML
     */
    protected static function __html( $TAG , $attributes = array() , $content = NULL ){
        if( isset( $attributes['class'])){
            if(is_array($attributes['class'])){
                $attributes['class'] = implode(' ', $attributes['class']);
            }
        }
        $serialized = array();
        foreach( $attributes as $var => $val ){
            $serialized[] = sprintf('%s="%s"',$var,$val);
        }
        if( !is_null($content) ){
            if(is_object($content)){
                $content = strval($content);
            }
            elseif(is_array($content)){
                $content = implode(' ', $content);
            }
            return sprintf('<%s %s>%s</%s>' , $TAG ,
                    implode(' ', $serialized) , strval( $content ) ,
                    $TAG);
        }
        return sprintf('<%s %s />' , $TAG , implode(' ', $serialized ) );
    }
    /**
     * @return string
     */
    public function name() {
        return $this->_name;
    }
    /**
     * @return string
     */
    public function id() {
        return $this->name();
    }
    /**
     * @return string
     */
    public function class() {
        return $this->_class;
    }
    /**
     * @return string
     */
    public function type(){
        return 'div';
    }
    
    /**
     * @return string
     */
    protected function path( ){
        return get_stylesheet_directory();
    }


    /**
     * @param string $class
     */
    protected function open( $class = '' ) {
        $class .= ' ' . $this->class();
        printf('<%s class="%s">', $this->type(),$class);
        if($this->hasWrapper()){
            printf('<div class="%s">',$this->wrap());
        }
    }
    /**
     * 
     */
    protected function close() {
        if( $this->hasWrapper()){
            print '</div>';
        }
        printf('</%s><!-- %s -->',$this->type(),$this->type());
    }
    /**
     * 
     */
    protected function content(){
        printf('<!-- %s -->',$this->name());
    }
    /**
     * @param string|array $class
     */
    public function render( $class = '' ){
        $this->open(is_array($class) ? implode(' ', $class) : $class );
        $this->content();
        $this->close();
    }
}

/**
 * 
 */
class Content extends Element{
    
    /**
     * @var \CODERS\Themes\Element[]
     */
    private $_contents = array();
    /**
     * 
     * @var string
     */
    private $_wrap = '';
    /**
     * @param string $name
     * @param string $class
     * @param string $wrap
     */
    public function __construct($name = '', $class = '', $wrap = '') {
        parent::__construct($name, $class );
        $this->_wrap = $wrap;
    }
    /**
     * @param string $content
     * @return bool
     */
    public function has($content = '' ){
        return strlen($content) && array_key_exists($content, $this->contents());
    }
    /**
     * @param Element $content
     * @return \CODERS\Themes\Content
     */
    protected function add(Element $content = null ){
        if(!is_null($content) && !$this->has($content->name())){
            $this->_contents[$content->name()] = $content;
        }
        return $this;
    }
    
    /**
     * @return \CODERS\Themes\Element[]
     */
    public function contents(){
        return $this->_contents;
    }
    /**
     * 
     */
    protected function content() {
        foreach( $this->contents() as $content ){
            $content->render();
        }
    }
    /**
     * @return string
     */
    protected function wrap(){
        return $this->_wrap;
    }
    /**
     * @return bool
     */
    protected function hasWrapper() {
        return strlen($this->_wrap) > 0;
    }
}
/**
 * 
 */
class Menu extends Element{
    /**
     * @var string
     */
    private $_title = '';
    /**
     * @var string
     */
    private $_location = '';
    
    /**
     * @param string $name
     * @param string $title
     */
    public function __construct($name = '', $location = '',$title = '') {
        parent::__construct($name,'menu');
        $this->_title = $title;
        $this->_location = $location;
    }
    /**
     * @return string
     */
    public function location() {
        return $this->_location;
    }
    /**
     * @return array
     */
    public function maplocation(){
        return array($this->name(),$this->location());
    }
    /**
     * @param string $class
     * @return array
     */
    protected function setup( $class = ''){
        return array(
                'theme_location' => $this->name(),
                'menu_class' => 'menu ' . (is_array($class) ? implode(' ', $class) : $class),
                'container' => FALSE,
                'echo' => FALSE);
    }
    /**
     * @param string $class
     */
    public function render($class = '' ) {
        if( has_nav_menu( $this->name() ) ){
            print wp_nav_menu($this->setup($class));
        }
    }
}
/**
 * 
 */
class Sidebar extends Element{
    /**
     * @var string
     */
    private $_title = '';
    
    public function __construct($name = '',$title = '') {
        parent::__construct($name, 'sidebar');
        $this->_title = strlen($title) ? $title : $name;
    }
    /**
     * @param string $h
     */
    public function register($h = 'h2'){
        register_sidebar(array(
            'before_widget'=>sprintf('<div class="widget"><!-- sidebar [%s] -->',$this->name()),
            'after_widget'=>sprintf('<!-- sidebar [%s] --></div>',$this->name()),
            'before_title'=>sprintf('<%s class="widget-title">',$ht),
            'after_title'=>sprintf('</%s>',$ht),
        ));
    }
    
    
    /**
     * @param string $class
     */
    protected function content() {
        dynamic_sidebar($this->name());
    }
}
/**
 * 
 */
class Logo extends Element{
    /**
     * @var bool
     */
    private $_display = false;
    /**
     * @param string $name
     */
    public function __construct($name = '') {
        parent::__construct($name, 'site-logo');
    }
    
    protected function content() {
        print function_exists( 'get_custom_logo' ) ?
                get_custom_logo() :
                self::__html('a', array(
                    'class' => 'theme-logo',
                    'href' => get_site_url(),
                    'target' => '_self'
                ), get_bloginfo('name'));
    }
}

/**
 * 
 */
class Post extends Element{
    
        /**
     * Define el tipo de post
     * @return array
     */
    protected function contentType( ){
        $post_type = get_post_type();
        switch( TRUE ){
            case $post_type === false ||is_404():
                return  array('error','404');
            case $post_type === 'page':
                return array('page','single');
            case $post_type === 'post':
                return array('post',is_single() ? 'single' : 'loop' );
        }
        return '';
    }
    /**
     * @param string $class
     */
    public function render($class = '') {
        $content_type = $this->contentType();
        $class .= ' content ' . $content_type;
        parent::render($class);
    }
    /**
     * 
     */
    protected function content() {
        $content_type = $this->contentType();
        $template = implode( '-', $content_type );
        $path = $this->template( $template );
        if(file_exists($path)){
            require $path;
        }
        else{
            $this->showNotFound($template);
        }
    }
    /**
     * @param string $name
     * @return string
     */
    protected function template( $name = '' ) {
        return sprintf('%s/html/%s.php',$this->path(),$name);
    }
}

/**
 * Move all theme setups here
 */
class Theme extends Content{
    /**
     * @var string
     */
    private $_path = '';
    private $_outline = array();
    private $_sidebars = array();
    private $_menus = array();
    
    /**
     * @param string $path
     */
    protected function __construct($path = '') {
        $this->_path = $path;
        
        parent::__construct('div', 'theme');
        
        foreach($this->template() as $template){
            if($this->load($template)){
                break;
            }
        }
        
        $this->setup();
    }
    /**
     * @return array
     */
    public function template() {
        return array(
            sprintf('%s/theme.json',$this->path()),
            sprintf('%s/theme.json', __DIR__)
        );
    }
    /**
     * @return type
     */
    protected function path(){
        return $this->_path;
    }
    /**
     * @return type
     */
    protected function outline(){
        return $this->_outline;
    }
    /**
     * @param string $uri
     * @return CoderThemes
     */
    public static final function create( $uri = '' ){
        return new Theme($uri);
            $root = explode('/', $uri );
            $name = $root[count($root)-1];
            $path = sprintf('%s/%s.theme.php',$uri,$name);
            $theme = sprintf('\CODERS\Themes\%sTheme', ucfirst( $name) );
            if(file_exists($path)){
                require_once $path;
                if(class_exists($theme) && is_subclass_of($theme, \CODERS\Themes\Theme::class,true)){
                    return new $theme($uri);
                }
                else{
                    printf('<p>Invalid Theme Instance %s</p>',$theme);
                }
            }
            else{
                printf('<p>Invalid Theme Path %s</p>',$path);
            }

            return null;
    }
    
    
    
    /**
     * @return \CODERS\Themes\Sidebar[]
     */
    public function sidebars() {
        return $this->_sidebars;
    }
    /**
     * @return \CODERS\Themes\Menu[]
     */
    public function menus() {
        return $this->_menus;
    }
    /**
     * @return array
     */
    public function extensions() {
        return array();
    }
    
    /**
     * @return array
     */
    public function scripts() {
        return array();
    }
    
    /**
     * @return array
     */
    public function styles() {
        return array();
    }
    
    
    /**
     * @return array
     */
    public function settings() {
        return array();
    }
    /**
     * @return array
     */
    public function customizers() {
        return array();
    }
    
    /**
     * @param string $name
     * @param string $title
     * @return \CODERS\Themes\Theme
     */
    public function sidebar($name = '',$title = '') {
        if(!array_key_exists($name, $this->sidebars())){
            $sidebar = new Sidebar($name,$title);
            $this->_sidebars[$sidebar->name()] = $sidebar;
        }
        return $this;
    }
    /**
     * @param string $name
     * @param string $loc
     * @param string $title
     * @return \CODERS\Themes\Theme
     */
    public function menu($name = '',$loc = '', $title = '') {
        if(!array_key_exists($name, $this->menus())){
            $menu = new Menu($name,$loc,$title);
            $this->_menus[$menu->name()] = $menu;
        }
        return $this;
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    protected function themesupport() {
        foreach($this->extensions() as $ext => $settings){
            add_theme_support($ext,$settings);
        }
        return $this;
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    protected function registermenus(){
        $locations = array_map( function($menu){
            return $menu->maplocation();
        },$this->menus());
        register_nav_menus($locations);
        return $this;
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    protected function registerSidebars(){
        foreach($this->sidebars() as $sb){
            $sb->register();
        }
        return $this;
    }
    /**
     * @param array $outline
     * @return \CODERS\Themes\Element[]
     */
    protected function read( array $outline = array() ) {
        $tree = array();
        foreach($outline as $name => $content ){
            $tree[$name] = is_numeric($name) ?
                    $this->read( $content ) :
                    $this->parse( $name , $content);
        }
        return $tree;
    }
    /**
     * @param string $name
     * @param array $content
     * @return \CODERS\Themes\Element
     */
    protected function parse( $name , $content ){
        switch(true){
            case $name === 'site-logo':
                return $content;
            case preg_match('/-menu$/', $name):
                $menu = substr($name,strlen($name)-5);
                return new Menu( $menu, $content['location'] ?? '', $content['title'] ?? '' );
            case preg_match('/-sidebar$/', $name):
                $sidebar = substr($name,strlen($name)-8);
                return new Sidebar( $sidebar, $content );
            case is_array($content):
                return $this->read($content);
        }
        return new Element($name);
    }


    /**
     * @param string $path
     * @return bool
     */
    private function load( $path = '') {
        if(file_exists($path)){
            $content = file_get_contents($path);
            if(strlen($content)){
                $this->_outline = json_decode($content, true);
                return true;
            }
        }
        return false;
    }
    
    /**
     * @return \CODERS\Themes\Theme
     */
    public function setup(){
        
        var_dump($this->outline());
        
        $this->themesupport()
                ->registerSidebars()
                ->registermenus();
        
        var_dump($this);
        return $this;
    }
    
    
    /**
     * @return string Título
     */
    protected function showTitle(){

        return is_front_page( /*inicio*/ ) || is_home( /*inicio o pagina de entradas*/) ?
                get_bloginfo( 'name' ) :    //solo titulo web
                get_bloginfo( 'name' ) . ' - ' . get_the_title( ); //titulo web + titulo  pagina
    }
    /**
     * @return \CODERS\Theme
     */
    protected function open( $class = ''){
        printf('<!DOCTYPE html><html %s>', get_language_attributes());
        print('<head>');
        printf('<title>%s</title>',$this->showTitle());
        wp_head();
        print('</head>');
        printf('<body class="%s" >', implode(' ',  get_body_class( ) ) );
        return $this;
    }
    /**
     * @return \CoderThemes
     */
    protected function close(){
        wp_footer();
        print '</html>';
        return $this;
    }
}






