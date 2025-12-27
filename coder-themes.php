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
 * Class: Theme
 ******************************************************************************/

// Just call do_action( 'coder_theme' , get_template_directory() ) and you are done :D
add_action( 'coder_theme', function( ){
    if(!is_admin()){
        \CODERS\Themes\Theme::show();
    }
});
add_action( 'init', function( ){
    $theme = get_template_directory();
    \CODERS\Themes\Theme::create($theme);
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
        $cls = array();
        if($this->class()){
            $cls[] = $this->class();
        }
        if( $class ){
            $cls[] = $class;
        }
        $id = $this->name() ? sprintf('id="%s"',$this->name()) : '';
        if(count($cls)){
            printf('<%s class="%s" %s><!-- %s opener -->', $this->type(), implode(' ', $cls),$id,$this);
        }
        else{
            printf('<%s %s><!-- %s opener -->', $this->type() , $id, $this );
        }
    }
    /**
     * 
     */
    protected function close() {
        printf('<!-- %s closer --></%s>',$this,$this->type());
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
    /**
     * @param string $name
     * @return \CODERS\Themes\Element
     */
    public static function empty($name = '' ) {
        return new Element($name,'empty');
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
        if(!is_null($content)){
            $this->_components[] = $content;
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
     * @param string $name
     * @param string $title
     */
    public function __construct($name = '', $title = '') {
        parent::__construct($name,'menu');
        $this->_title = $title;
    }
    /**
     * @return string
     */
    public function title() {
        return $this->_title;
    }
    /**
     * @return array
     */
    public function maplocation(){
        return array($this->name(),$this->title());
    }
    /**
     * @param string $class
     * @return array
     */
    protected function setup( $class = ''){
        $cls = array('menu');
        if($class){
            $cls[] = $class;
        }
        return array(
                'theme_location' => $this->title(),
                'menu_class' => implode(' ', $cls),
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
    /**
     * @var string
     */
    private $_desc = '';
    /**
     * @param string $name
     * @param string $title
     * @param string $desc
     */
    public function __construct($name = '',$title = '', $desc = '') {
        parent::__construct($name, 'sidebar');
        $this->_title = strlen($title) ? $title : $name;
        $this->_desc = $desc;
    }
    /**
     * @return string
     */
    protected function title() {
        return $this->_title;
    }
    /**
     * @return string
     */
    protected function desc() {
        return $this->_desc;
    }
    /**
     * @param string $ht
     * @return \CODERS\Themes\Sidebar
     */
    public function register( $ht = 'h2'){
        $sidebar = array(
            'name' => $this->title(),
            'id' => $this->name(),
            'description' => $this->desc(),
            'before_widget'=>sprintf('<div class="widget"><!-- sidebar [%s] -->',$this->name()),
            'after_widget'=>sprintf('<!-- sidebar [%s] --></div>',$this->name()),
            'before_title'=>sprintf('<%s class="widget-title">',$ht),
            'after_title'=>sprintf('</%s>',$ht),
        );
        register_sidebar($sidebar);
        return $this;
    }
    
    
    /**
     * @param string $class
     */
    protected function content() {
        dynamic_sidebar( $this->name());
    }
}
/**
 * 
 */
class Logo extends Element{
    /**
     * @param string $name
     */
    public function __construct($name = '') {
        parent::__construct($name, 'site-logo');
    }
    /**
     * @param  string $class
     */
    public function render( $class = '') {
        $cls = array('theme-logo');
        if( $class){
            $cls[] = $class;
        }
        print function_exists( 'get_custom_logo' ) ?
                get_custom_logo() :
                self::__html('a', array(
                    'class' => implode(' ', $cls),
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
    protected function postType( ){
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
        $css = $this->postType();
        $css[] = 'content';
        if(strlen($class)){
            $css[] = $class;
        }
        parent::render(implode(' ', $css));
    }
    /**
     * 
     */
    protected function content() {
        $content_type = $this->postType();
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
     * @var \CODERS\Themes\Theme
     */
    private static $_theme = null;
    /**
     * @var string[]
     */
    private $_route = array();
    private $_layout = array();
    private $_sidebars = array();
    private $_menus = array();
    private $_settings = array();
    private $_block = ['container'];
    private $_ids = array();
    
    /**
     * @param string $path
     * @param string $class
     * @param string $wrap
     */
    protected function __construct($path = '',$class = '' , $wrap = 'wrap' ) {
        
        $this->_route = explode('/', $path );
        
        parent::__construct('theme',$class,$wrap);
        
        $this->setup();
    }
    /**
     * @return string
     */
    private function templates() {
        $theme = sprintf('%s/theme.json',$this->path());
        if(file_exists($theme)){
            return $theme;
        }
        $local = sprintf('%s/theme.json', preg_replace('/\\\\/', '/', __DIR__));
        
        return file_exists($local) ? $local : '';
    }
    /**
     * @param boolean $parse
     * @return string|Array
     */
    protected function cssblock( $parse = false ){
        return $parse ? implode(' ', $this->_block) : $this->_block;
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
    private function themename() {
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
     * @return \CODERS\Themes\Theme
     */
    public static final function create( $uri = '' ){
        self::$_theme = new Theme( preg_replace('/\\\\/', '/', $uri));
        return self::$_theme;
    }
    /**
     * @param string $class
     * @return \CODERS\Themes\Theme
     */
    public static function show($class = '') {
        if( self::$_theme){
            self::$_theme->render( $class );
        }
    }
    /**
     * @return \CODERS\Themes\Sidebar[]
     */
    public function sidebars() {
        return $this->_sidebars;
    }
    /**
     * @param string $name
     * @return \CODERS\Themes\Sidebar
     */
    protected function sidebar($name) {
        $sb =  $this->sidebars()[$name] ?? null;
        //var_dump(sprintf('%s [%s]',$name,$sb));
        return $sb;
    }
    /**
     * @return \CODERS\Themes\Menu[]
     */
    public function menus() {
        return $this->_menus;
    }
    /**
     * @param string $name
     * @return \CODERS\Themes\Menu
     */
    protected function menu($name) {
        $menu = $this->menus()[$name] ?? null;
        //var_dump(sprintf('%s [%s]',$name,$menu));
        return $menu;
    }
    /**
     * @return array
     */
    protected function settings() {
        return $this->_settings;
    }
    /**
     * @return array
     */
    public function customizers() {
        return array();
    }
    /**
     * @todo link sidebars and menus from the registered theme components
     * @param string $name
     * @param mixed $content
     * @return \CODERS\Themes\Element
     */
    protected function parse( $name , $content = '' ){
        $css = $this->cssblock(true);
        $wrap = $this->wrap();
        switch(true){
            case is_array($content):
                $block = new Content($name,$css);
                foreach ($content as $key => $data ){
                    $block->add($this->parse(
                            is_numeric($key) ? '' : $key,
                            $data,
                            $wrap ) );
                }
                return $block;
            case $content === 'content':
            case $content === 'blog':
                return new Post($name);
            case $content === 'site-logo':
                return $this->attachLogo($name, $content);
            case preg_match('/-menu$/', $content):
                return $this->attachMenu($name, $content);
            case preg_match('/-sidebar$/', $content):
                return $this->attachSidebar($name, $content);
        }
        return new Element($name , $css);
    }
    /**
     * @param string $wrapper
     * @param string $name
     * @return \CODERS\Themes\Content
     */
    protected function attachMenu($wrapper,$name) {
        $block = new Content('',$wrapper);
        $block->add($this->menu(substr($name, 0 , strlen($name)-5)) ?? Element::empty($name));
        return $block;
    }
    /**
     * @param string $wrapper
     * @param string $name
     * @return \CODERS\Themes\Content
     */
    protected function attachSidebar($wrapper,$name) {
        $block = new Content('',$wrapper);
        $block->add($this->sidebar(substr($name, 0 , strlen($name)-8)) ?? Element::empty($name));
        return $block;
    }
    /**
     * @param string $wrapper
     * @param string $name
     * @return \CODERS\Themes\Content
     */
    protected function attachLogo($wrapper,$name) {
        $block = new Content('',$wrapper);
        $block->add(new Logo($name));
        return $block;
    }
    /**
     * @param array $support
     * @return \CODERRS\Themes\Theme
     */
    private function readSettings( $support = array()) {
        foreach($support as $type => $content){
            switch($type){
                case 'ids':
                    $this->_ids = $content;
                    break;
                case 'style':
                    $this->readStyles($content);
                    break;
                case 'script':
                    $this->readScripts($content);
                    break;
                default:
                    $this->_settings[$type] = $content;
                    add_theme_support($type, $content);
                    break;
            }
        }
        return $this;
    }
    /**
     * @param array $menus
     * @return \CODERRS\Themes\Theme
     */
    private function readMenus( $menus = array()) {
        foreach($menus as $name => $title ){
            $rname = trim($name);
            $this->_menus[ $rname ] = new Menu($rname, $title);
        }
        $map = array();
        foreach($this->menus() as $menu ){
            $map[$menu->name()] = $menu->title();
        }
        register_nav_menus($map);
        return $this;
    }
    /**
     * @param array $sidebars
     * @return \CODERRS\Themes\Theme
     */
    private function readSidebars( $sidebars = array()) {
        foreach($sidebars as $name => $content ){
            $rname = trim($name);
            $sb = new Sidebar(
                    trim( $rname ),
                    $content['title'] ?? $rname ,
                    $content['desc'] ?? ''
                );
            $this->_sidebars[ $rname ] = $sb->register();
        }
        return $this;
    }
    /**
     * @param array $script
     * @return \CODERS\Themes\Theme
     */
    private function readScripts($script = array()) {
        $list = array();
        foreach ($script as $name) {
            $handle = sprintf('%s-%s',$this->themename(),$name);
            $list[$handle] = sprintf('%s/%s.js', $this->url(), $name);
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
    private function readStyles( $styles = array()) {
        $list = array();
        foreach ($styles as $name) {
            $handle = sprintf('%s-%s',$this->themename(),$name);
            $list[$handle] = sprintf('%s/%s.css', $this->url(), $name);
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
     * @return array
     */
    private function load( ) {
        $template = $this->templates();
        if(strlen($template ) ){
            $content = file_get_contents($template );
            if(strlen($content)){
                return json_decode($content, true);
            }
        }
        return array();
    }
    
    /**
     * @return \CODERS\Themes\Theme
     */
    public function setup(){
        //return $this;
        $template = $this->load();
        //support
        $this->readSettings($template['settings'] ?? array());
        $this->readMenus($template['menu'] ?? array());
        $this->readSidebars($template['sidebar'] ?? array());
        //layout
        $this->_layout = $template['layout'] ?? array();
        return $this;
    }
    
    
    /**
     * @return string Título
     */
    protected function title(){

        return is_front_page( /*start*/ ) || is_home( /*front page or entry loop*/) ?
                get_bloginfo( 'name' ) :    //main title only
                get_bloginfo( 'name' ) . ' - ' . get_the_title( ); //context + page title
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    protected function open( $class = ''){
        
        $classname = get_body_class();
        $classname[] = 'coder-themes ' . $this->themename();
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
     * @return \CODERS\Themes\Theme
     */
    protected function close(){
        wp_footer();
        //print '</html>';
        return $this;
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    protected function prepare() {
        foreach($this->layout() as $name => $content ){
            $this->add($this->parse($name , $content ) );
        }        
        return $this;
    }
    /**
     * @param string $class
     * @return \CODERS\Themes\Theme
     */
    public function render($class = '') {
        $this->prepare();
        //parent::render($class);
        $this->open($class);
        $this->content();
        $this->close();
        return $this;
    }
}



