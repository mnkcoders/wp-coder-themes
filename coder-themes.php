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
//Call to load the theme
add_action( 'coder_theme_load' , function( ){
    \CODERS\Themes\Theme::create(get_template_directory());
});



/**
 * Create extended content blocks to render each content
 */
class Element{
    /**
     * @var array
     */
    private $_att = array(
        'type' => 'div',
        'name' => '',
        'class' => '',
        'id' => '',
    );
    
    /**
     * @param string $name
     * @param string $class
     */
    public function __construct($name = '' , $class = '' ) {
        $this->set('name', $name );
        $this->set('class',$class);
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
        return $this->_att[$name] ?? '';
        //return sprintf('<!-- %s -->',$name);
    }
    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name , $value ) {
        if($this->has($name)){
            $this->_att[$name] = $value;
        }
    }
    /**
     * @param string $name
     * @return boolean
     */
    protected function has($name) {
        return array_key_exists($name, $this->_att );
    }
    /**
     * @param string $name
     * @param string $value
     * @return \CODERS\Themes\Element
     */
    protected function set($name,$value = '') {
        if( $name ){
            $this->_att[$name] = $value;
        }
        return $this;
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
        return $this->name;
    }
    /**
     * @return string
     */
    public function id() {
        return $this->id;
    }
    /**
     * @return string
     */
    public function class() {
        return $this->class;
    }
    /**
     * @return string
     */
    public function type(){
        return $this->type;
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
        $id = $this->id() ? sprintf('id="%s"',$this->id()) : '';
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
class Customizer{
    
    private $_settings = array();
    private $_sections = array();
    private $_priority = 0;
    /**
     * @param string $template
     * @param int $priority
     */
    public function __construct( $priority = 0) {
        $this->_priority = $priority;
    }
    /**
     * @param string $name
     * @return string
     */
    public function __get($name) {
        return $this->get($name);
    }
    /**
     * @return int
     */
    private function priority() {
        return $this->_priority;
    }
    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name= '', $default = '') {
        return get_theme_mod($name, $default);
    }
    /**
     * @param \CODERS\Themes\Section $section
     * @return \CODERS\Themes\Customizer
     */
    public function addsection( Section $section = null ) {
        if($section){
            $this->_sections[$section->name()] = $section;
            return $section;
        }
        return null;
    }
    /**
     * @param \CODERS\Themes\Setting $setting
     * @return \CODERS\Themes\Customizer
     */
    public function addsetting(Setting $setting = null ) {
        if($setting){
            $this->_settings[$setting->name()] = $setting;
        }
        return $this;
    }
    /**
     * @return \CODERS\Themes\Setting[]
     */
    public function settings() {
        return $this->_settings;
    }
    /**
     * @return \CODERS\Themes\Section[]
     */
    public function sections(){
        return $this->_sections;
    }
    
    /**
     * 
     * @return \CODERS\Themes\Customizer
     */
    public function setup(){
        
        $customizer = $this;
        
        add_action('customize_register', function(WP_Customize_Manager $wp_customize) use($customizer){
            
            foreach( $customizer->settings() as $id => $settings ){
                $wp_customize->add_setting($id, $settings);
            }
            
            foreach( $customizer->sections() as $id => $section ){
                $wp_customize->add_section($id,$section);
            }
            
            foreach( $customizer->controls() as $id => $control ){
                
                if(array_key_exists('type', $control) && $control['type'] === 'select' ){
                    $control['choices'] = $control->setting($id);
                }
                
                $wp_customize->add_control(new WP_Customize_Control(
                        $wp_customize,
                        $id,
                        $control->contents()
                        ));
            }
        });
        
        return $this;
    }
    /**
     * @param array $content
     * @return \CODERS\Themes\Customizer
     */
    public static function read( array $content = array()) {
            $customizer = new Customizer();
            $priority = $customizer->priority();
            $settings = $content['settings'] ?? array();
            $sections = $content['sections'] ?? array();
            foreach($settings as $setting  ){
                $customizer->addsetting( Setting::read($setting));
            }
            foreach($sections as $section){
                $s = Section::read($section);
                $controls = $section['controls'] ?? array();
                foreach($controls as $control ){
                    $c = Control::read($control);
                    if( $c ){
                        $c->fromsetting($customizer->settings()[$c->settings] ?? null);
                        $s->add($c, $priority );
                    }
                }
                $customizer->addsection( $s );
            }
            return $customizer->setup();
    }
}
/**
 * 
 */
class Setting{
    const SELECT = 'select';
    const TEXT = 'text';
    const NUMBER = 'number';
    const CHECKBOX = 'checkbox';

    private $_type = self::TEXT;
    private $_name = 'setting';
    private $_contents = array();
    /**
     * @param string $name
     * @param string $type
     * @param array $values
     */
    protected function __construct($name , $type = self::TEXT , $values = array()) {
        $this->_name = $name;
        $this->_type = $type;
        $this->_contents = $values;
    }
    /**
     * @param string $name
     * @param array $values
     * @return \CODERS\Themes\Setting
     */
    public static function read( array $setting = array() ) {
        $name = $setting['setting'] ?? '';
        return $name ? new Setting(
                $name,
                $setting['type'] ?? self::TEXT,
                $setting['values'] ?? array()) : null;
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
    public function type() {
        return $this->_type;
    }
    /**
     * @return array
     */
    public function valules() {
        return $this->_contents;
    }
    /**
     * @return string[]
     */
    public function list() {
        return array_keys($this->valules());
    }
    /**
     * @param string $default
     */
    public function getmod( $default = ''){
        return get_theme_mod($this->name(),$default);        
    }
}

/**
 * 
 */
class Section {
    /**
     * @var array
     */
    private $_section = array(
        'id' => 'section',
        'title' => 'Section',
        'priority' => 0,
    );
    /**
     * @var \CODERS\Themes\Control[]
     */
    private $_controls = array();
    /**
     * @param string $id
     * @param string $title
     * @param int $priority
     */
    public function __construct( $id = '' , $title = '' , $priority = 0) {
         $this->id = $id;
         $this->title = $title;
         $this->priority = $priority;
    }
    /**
     * @param array $section
     * @param int $priority
     * @return \CODERS\Themes\Section
     */
    public static function read( array $section = array() , $priority = 0) {
        return new Section(
                $section['id'] ?? 'section',
                $section['title'] ?? 'Section',
                $section['priority'] ?? 0
        );
    }
    /**
     * @return string
     */
    public function name() {
        return $this->id;
    }
    /**
     * @param string $name
     * @return string
     */
    public function __get($name) {
        return $this->_section[$name] ?? '';
    }
    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name,$value) {
        if( $this->has($name)){
            $this->_section[$name] = $value;
        }
    }
    /**
     * @param string $name
     * @return boolean
     */
    public function has($name) {
        return array_key_exists($name, $this->_section);
    }
    /**
     * @param \CODERS\Themes\Control $control
     * @param int $priority
     * @return \CODERS\Themes\Section
     */
    public function add( $control = null , $priority = 0) {
        if( $control && get_class($control) === Control::class ){
            $count = $priority + count($this->controls()) + 1;
            $control->priority = $count;
            $control->section = $this->name();
            $this->_controls[$control->name()] = $control; 
        }
        return $this;
    }
    /**
     * @return \CODERS\Themes\Control[]
     */
    public function controls() {
        return $this->_controls;
    }
    /**
     * @return array
     */
    public function section() {
        return $this->_section;
    }
}

/**
 * 
 */
class Control{
    const SELECT = 'select';
    const TEXT = 'text';
    const NUMBER = 'number';
    const CHECKBOX = 'checkbox';

    /**
     * @var array
     */
    private $_control = array(
        'id' => 'control',
        'section' => '',
        'settings' => '',
        'label' => '',
        //'type' => '',
        'description' => '',
        'priority' => 0,
    );
    /**
     * @var \CODERS\Themes\Setting
     */
    private $_setting = null;
   
    /**
     * @param array $contents
     */
    public function __construct( $contents = array() ) {
        $this->populate($contents);
    }
    /**
     * @param array $contents
     * @return \CODERS\Themes\Control
     */
    private function populate( $contents = array()) {
        foreach($contents as $key => $val ){
            $this->$key =$val;
        }
        return $this;
    }
    /**
     * @param array $content
     * @return \CODERS\Themes\Control
     */
    public static function read( array $content = array() ) {
        return new Control($content);
    }
    /**
     * @return string
     */
    public function name() {
        return $this->id;
    }
    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name,$value) {
        if( $this->has($name)){
            $this->_control[$name] = $value;
        }
    }
    /**
     * @param string $name
     * @return string
     */
    public function __get($name) {
        return $this->_control[$name] ?? '';
    }
    /**
     * @param string $name
     * @return boolean
     */
    public function has($name) {
        return array_key_exists($name, $this->_control);
    }
    /**
     * @return \CODERS\Themes\Setting
     */
    public function setting() {
        return $this->_setting;
    }
    /**
     * @param int $priority
     * @return array
     */
    public function contents( ) {
        $setting = $this->setting();
        $content = $this->_control;
        $content['type'] = $setting ? $setting->type() : Setting::TEXT;
        $values = $setting ? $setting->valules() : array();
        if(count($values) ){
            $content['choices'] = $values;
        }
        
        return $content;
    }
    /**
     * @param \CODERS\Themes\Setting $setting
     * @return \CODERS\Themes\Control
     */
    public function fromsetting( Setting $setting  =null) {
        $this->_setting = $setting ? $setting : null;
        return $this;
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
        $this->set('wrap',$wrap);
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
     * @param string $name
     * @param string $title
     * @param string $desc
     */
    public function __construct($name = '',$title = '', $desc = '') {
        parent::__construct($name, 'sidebar');
        $this->set('title',strlen($title) ? $title : $name)
            ->set('description',$desc);
    }
    /**
     * @return string
     */
    protected function title() {
        return $this->title;
    }
    /**
     * @return string
     */
    protected function desc() {
        return $this->description;
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
    private $_ids = array();
    /**
     * @var string[]
     */
    private $_block = ['container'];
    /**
     * @var \CODERS\Themes\Customizer
     */
    private $_customizer = null;
    
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
     * @return String[]
     */
    protected function  ids(){
        return $this->_ids;
    }
    /**
     * @param string $name
     * @return boolean
     */
    protected function isid($name = '') {
        return $name ? in_array($name, $this->ids()) : false;
    }
    /**
     * @param string  $setting
     * @param mixed $default
     */
    public function mod($setting = '',$default = '') {
        return $this->customizer() ? $this->customizer()->get($setting,$default) : $default;
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
     * @param string $path
     * @return string
     */
    private static function themepath( $path = ''){
        return sprintf('%s/theme.json',$path);
    }
    /**
     * @return string
     */
    private function templatepath() {
        return self::themepath($this->path());
    }
    /**
     * @return boolean
     */
    private function ready() {
        return file_exists($this->templatepath());
    }
    /**
     * @return type
     */
    protected function layout(){
        return $this->_layout;
    }
    /**
     * @return \CODERS\Themes\Theme
     */
    public static function theme() {
        return self::$_theme;
    }
    /**
     * @param string $uri
     * @return \CODERS\Themes\Theme
     */
    public static final function create( $uri = '' ){
        $path = preg_replace('/\\\\/', '/', $uri);
        $themeurl = self::themepath($path);
        if(file_exists($themeurl)){
            self::$_theme = new Theme( $path );
        }
        return self::theme();
    }
    /**
     * @param string $class
     * @return \CODERS\Themes\Theme
     */
    public static function show($class = '') {
        if( self::$_theme ){
            self::$_theme->render( $class );
        }
        else{
            printf('No theme template here :(');
        }
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
     * @todo link sidebars and menus from the registered theme components
     * @param string $name
     * @param mixed $content
     * @return \CODERS\Themes\Element
     */
    protected function parse( $name , $content = '' ){
        $css = $this->cssblock(true);
        $wrap = $this->wrap();
        $block = new Content($name,$css);
        if( $this->isid($name)){
            $block->id = $name;
        }
        switch(true){
            case is_array($content):
                foreach ($content as $key => $data) {
                    $block->add($this->parse(
                                    is_numeric($key) ? '' : $key,
                                    $data,
                                    $wrap));
                }
                break;
            case $content === 'content':
            case $content === 'blog':
                $block->add(new Post($name));
                break;
            case $content === 'site-logo':
                $block->add(new Logo($name));
                break;
            case preg_match('/-menu$/', $content):
                $menu = substr($content, 0 , strlen($content)-5);
                $block->add( $this->menus()[$menu] ?? new Element($content,'empty') );
                break;
            case preg_match('/-sidebar$/', $content):
                $sb = substr($content, 0 , strlen($content)-8);
                $block->add( $this->sidebars()[$sb] ?? new Element($content,'empty'));
                break;
        }
        return $block;
        //return new Element($name , $css  );
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
        //$template = $this->templates();
        if( $this->ready()){
            $content = file_get_contents($this->templatepath());
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
        
        if(is_admin() ){
            $this->_customizer = Customizer::read( $template['customizer']  ?? array() );
        }
        
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


