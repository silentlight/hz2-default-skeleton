<?php
use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

class TestBootstrap
{
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var ServiceManager
	 */
    protected $sm;
	
	/**
	 * @var TestBootstrap
	 */
	protected static $app = NULL;
	
	/**
	 * @var string
	 */
	const APPLICATION_PATH = "C:/wamp/www/Tutorials/Web_Developer/PHP/Zend2/";
	
	/**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) 
    {
        return $this->$property;
    }
 
    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) 
    {
        $this->$property = $value;
    }
	
	/**
	 * Singleton
	 * 
	 * @return TestBootstrap
	 */
	public static function getApp()
	{
		if(!isset(self::$app)){
			self::$app = new TestBootstrap();
		}
		
		return self::$app;
	}
	
    private function __construct()
    {
		//initialize autoloaders
        $this->initAutoloader();
		
        // Load the user-defined test configuration file
        if (is_readable(__DIR__ . '/TestConfig.php')) {
            $test_config = include __DIR__ . '/TestConfig.php';
        } else {
            $test_config = include __DIR__ . '/TestConfig.php.dist';
        }
		
		//bootstrap application
        $main_config = require self::APPLICATION_PATH . 'config/application.config.php';
		
        $config = ArrayUtils::merge($main_config, $test_config);
	
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        $this->sm = $serviceManager;
		
		//initialize doctrine
        $this->initDoctrine($serviceManager);
    }

    protected function initAutoloader()
    {
        $vendorPath = $this->findParentPath('vendor');
		
		// Composer autoloading
		if (file_exists($vendorPath . '/autoload.php')) {
		    $loader = include $vendorPath . '/autoload.php';
		}
		
        $zf2Path = getenv('ZF2_PATH');
        if (!$zf2Path) {
            if (defined('ZF2_PATH')) {
                $zf2Path = ZF2_PATH;
            } else {
                if (is_dir($vendorPath . '/zendframework/zendframework/library')) {
                    $zf2Path = $vendorPath . '/zendframework/zendframework/library';
                }
            }
        }

        if (!$zf2Path) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
        }

        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                ),
            ),
        ));
    }

    protected function initDoctrine($serviceManager)
    {
        $serviceManager->setAllowOverride(true);
        $config = $serviceManager->get('Config');
		
		$doctrine_config = include self::APPLICATION_PATH . '/config/autoload/testing/doctrine.local.php';
		
        $config['doctrine']['connection']['orm_default'] = $doctrine_config['doctrine']['connection']['orm_default'];

        $serviceManager->setService('Config', $config);
        
        $this->em = $serviceManager->get('Doctrine\ORM\EntityManager');
		
		$this->beforeSuite();
    }

    protected function findParentPath($path)
    {
        $dir         = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir         = dirname($dir);
            if ($previousDir === $dir)
                return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
	
	public function beforeSuite()
	{
		$this->tool = new SchemaTool($this->em);
		
		//first drop schema
		$this->dropSchema();
		
		//then create new schema
		$metas = $this->getAllClassMetas();
		$this->tool->createSchema($metas);
	}
	
	public function afterSuite()
	{
		//drop schema
		$this->dropSchema();
	}
	
	/**
	 * Drop existing schema
	 */
	public function dropSchema()
	{
		$this->tool->dropDatabase();	
	}
	
	/**
	 * Get array of class metas from all modules containing entities
	 * 
	 * @return array
	 */
	public function getAllClassMetas()
	{
		$metas = array();
		
		$module_path = self::APPLICATION_PATH . 'module';
		
		if($handle = opendir($module_path)){
			while(false !== ($module_dir = $module_namespace = readdir($handle))){
				
				$module_dir_path = $module_path . DIRECTORY_SEPARATOR . $module_dir;
				
				//skip "." and ".."
				if(is_dir($module_dir_path) && ($module_dir != "." && $module_dir != "..")){
					
					$module_entity_path = $module_dir_path . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $module_namespace . DIRECTORY_SEPARATOR . 'Entity'; 
					$namespace = $module_namespace . "\\Entity\\";
					
					if(is_dir($module_entity_path)){
						$metas += $this->getClassMetas($module_entity_path, $namespace);
					}
					
				}
			}
		}
		
		return $metas;
	}
	
	/**
	 * Get array of class metas for given entity
	 * 
	 * @param string $path
	 * @param string $namespace
	 * 
	 * @return array
	 */
	public function getClassMetas($path, $namespace)
	{
		$metas = array();
		if($handle = opendir($path)){
			
			while(false !== ($file = readdir($handle)))
			{
				if(strstr($file, '.php')){
					list($class) = explode('.', $file);
					$metas[] = $this->em->getClassMetadata($namespace . $class);
				}
			}
		}
		
		return $metas;
	}
}
