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
        \CODERS\Themes\Theme::create($theme_path)->render();
    }
});





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
        $element = array($this->type());
        if( $this->name() ){
            $element[] .= '#'.$this->name();
        }
        if( $this->class()){
            $element[] .= '.' . $this->class();
        }
        return implode('', $element);
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
        /*if($this->hasWrapper()){
            printf('<div class="%s">',$this->wrap());
        }*/
    }
    /**
     * 
     */
    protected function close() {
        /*if( $this->hasWrapper()){
            print '</div>';
        }*/
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
    private $_components = array();
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
        return strlen($content) && array_key_exists($content, $this->components());
    }
    /**
     * @param Element $content
     * @return \CODERS\Themes\Content
     */
    protected function add(Element $content = null ){
        if(!is_null($content) && !$this->has($content->name())){
            $this->_components[$content->name()] = $content;
        }
        return $this;
    }
    
    /**
     * @return \CODERS\Themes\Element[]
     */
    public function components(){
        return $this->_components;
    }
    /**
     * 
     */
    protected function content() {
        foreach( $this->components() as $content ){
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
    public function hasWrapper() {
        return strlen($this->_wrap) > 0;
    }
    
    /**
     * @param string $class
     */
    protected function open($class = '') {
        parent::open( $class );
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
        parent::close();
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
     * @param string $ht
     * @return \CODERS\Themes\Sidebar
     */
    public function register( $ht = 'h2'){
        register_sidebar(array(
            'name' => $this->name(),
            'id' => $this->id(),
            'before_widget'=>sprintf('<div class="widget"><!-- sidebar [%s] -->',$this->name()),
            'after_widget'=>sprintf('<!-- sidebar [%s] --></div>',$this->name()),
            'before_title'=>sprintf('<%s class="widget-title">',$ht),
            'after_title'=>sprintf('</%s>',$ht),
        ));
        return $this;
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
     * @var string[]
     */
    private $_route = array();
    private $_layout = array();
    private $_sidebars = array();
    private $_menus = array();
    private $_support = array();
    
    /**
     * @param string $path
     */
    protected function __construct($path = '') {
        
        $this->_route = explode('/', $path );
        
        parent::__construct('theme');
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    private function preload() {
        foreach($this->template() as $template){
            if($this->load($template)){
                break;
            }
        }
        return $this;
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
     * @return String[]
     */
    protected function route() {
        return $this->_route;
    }
    /**
     * @return string
     */
    protected function path(){
        return implode('/', $this->route());
    }
    /**
     * @return string
     */
    protected function url(){
        return get_template_directory_uri();
    }
    /**
     * @return string
     */
    protected function theme() {
        return $this->route()[count($this->route())-1];
    }
    /**
     * @return type
     */
    protected function layout(){
        return $this->_layout;
    }
    /**
     * @param string $uri
     * @return CoderThemes
     */
    public static final function create( $uri = '' ){
        $theme = new Theme($uri);
        
        return $theme->preload()->setup();
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
        return $this->_support;
    }
    
    /**
     * @return array
     */
    public function customizers() {
        return array();
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    protected function themesupport() {
        foreach($this->extensions() as $ext => $settings){
            if(is_array($settings)){
                add_theme_support($ext,$settings);
            }
        }
        return $this;
    }
    /**
     * @param mixed $outline
     * @return \CODERS\Themes\Element[]
     */
    protected function read( array $outline = array() ) {
        $tree = array();
        foreach($outline as $name => $content ){
            $tree[$name] = $this->parse( $name , $content);
        }
        return $tree;
    }
    /**
     * @param string $name
     * @param mixed $content
     * @return \CODERS\Themes\Element
     */
    protected function parse( $name , $content = '' ){
        switch(true){
            case is_numeric($name):
                return $this->parse($content);
            case $name === 'site-logo':
                return new Logo();
            case preg_match('/-menu$/', $name):
                $menu = substr($name,strlen($name)-5);
                return new Menu( $menu, $content['location'] ?? '', $content['title'] ?? '' );
            case preg_match('/-sidebar$/', $name):
                $sidebar = substr($name,strlen($name)-8);
                return new Sidebar( $sidebar, $content );
            case is_array($content):
                $container = new Content($name);
                foreach ($content as $key => $data ){
                    $container->add($this->parse($key,$data));
                }
                return $container;
        }
        return new Element($name);
    }
    /**
     * @param array $support
     * @return \CODERRS\Themes\Theme
     */
    private function loadExtensions( $support = array()) {
        foreach($support as $type => $content){
            $this->_support[$type] = $content;
        }
        return $this->themesupport();
    }
    /**
     * @param array $menus
     * @return \CODERRS\Themes\Theme
     */
    private function loadMenus( $menus = array()) {
        foreach($menus as $location => $name ){
            $menu = new Menu($name, $location);
            $this->_menus[ $name ] = $menu;
        }
        $map = array();
        foreach($this->menus() as $menu ){
            $map[$menu->location()] = $menu->name();
        }
        register_nav_menus($map);
        return $this;
    }
    /**
     * @param array $sidebars
     * @return \CODERRS\Themes\Theme
     */
    private function loadSidebars( $sidebars = array()) {
        foreach($sidebars as $name => $title ){
            $sb = new Sidebar($name,$title);
            $this->_sidebars[ $name ] = $sb->register();
        }
        return $this;
    }
    /**
     * @param array $script
     * @return \CODERS\Themes\Theme
     */
    private function loadScripts($script = array()) {
        $list = array();
        foreach ($script as $name) {
            $list[$name] = sprintf('%s/%s.js', $this->url(), $name);
        }
        if( count($list)){
            add_action( 'wp_enqueue_scripts' , function() use($list){
                foreach($list as $handle => $url ){
                    wp_enqueue_script( $handle, $url,array(),false,true);
                }
            });
        }
        return $this;
    }
    /**
     * @param array $styles
     * @return \CODERS\Themes\Theme
     */
    private function loadStyles( $styles = array()) {
        $list = array();
        foreach ($styles as $name) {
            $list[$name] = sprintf('%s/%s.css', $this->url(), $name);
        }
       if( count($list)){
            add_action( 'wp_enqueue_scripts' , function() use($styles){
                foreach($styles as $handle => $url ){
                    wp_enqueue_style( $handle, $url,array(),false,true);
                }
            });
        }
        return $this;
    }


    /**
     * @param string $path
     * @return bool
     */
    private function load( $path = '') {
        if(file_exists($path)){
            $content = file_get_contents($path);
            if(strlen($content)){
                $this->_layout = json_decode($content, true);
                return true;
            }
        }
        return false;
    }
    
    /**
     * @return \CODERS\Themes\Theme
     */
    public function setup(){
        //return $this;
        $setup = $this->layout();
        //support
        $this->loadExtensions($setup['support'] ?? array());
        //style and scripts
        $this->loadScripts($setup['script'] ?? array());
        $this->loadStyles($setup['style'] ?? array());
        $this->loadMenus($setup['menu'] ?? array());
        $this->loadSidebars($setup['sidebar'] ?? array());
        //layout
        $contents = $this->parse($this->theme(),$setup['layout'] ?? array());
        foreach($contents->components() as $c ){
            $this->add($c);
        }
        var_dump($this->components());
        return $this;
    }
    
    
    /**
     * @return string Título
     */
    protected function title(){

        return is_front_page( /*inicio*/ ) || is_home( /*inicio o pagina de entradas*/) ?
                get_bloginfo( 'name' ) :    //solo titulo web
                get_bloginfo( 'name' ) . ' - ' . get_the_title( ); //titulo web + titulo  pagina
    }
    /**
     * @return \CODERS\Theme
     */
    protected function open( $class = ''){
        
        $classname = get_body_class();
        $classname[] = 'coder-themes ' . $this->theme();
        if($class ){
            $classname[] = $class;
        }
        
        printf('<!DOCTYPE html><html %s>', get_language_attributes());
        printf('<head><title>%s</title>',$this->title());
        wp_head();
        print('</head>');
        //printf('<body class="%s" >', implode(' ',  $classname ) );
        return $this;
    }
    /**
     * @return \CoderThemes
     */
    protected function close(){
        wp_footer();
        //print '</html>';
        return $this;
    }
    /**
     * @param string $class
     * @return \CODERS\Themes\Theme
     */
    public function render($class = '') {
        //parent::render($class);
        $this->open($class);
        var_dump($this->components());
        $this->content();
        //print($this);
        //var_dump($this);
        
        $this->close();
        return $this;
    }
}



