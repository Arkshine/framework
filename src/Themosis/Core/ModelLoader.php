<?php

namespace Themosis\Core;

use ReflectionClass;

class ModelLoader extends Loader implements LoaderInterface
{

    /**
     * The model extension
     * 
     * @var string
    */
    private static $ext = '.model';

    /**
     * The model name suffix
     * 
     * @var string
    */
    private static $suffix = '_Model';


    /**
     * Check if the class is extending BaseModel class before doing
     * any actions with it. Force the developer to always extends its model
     * class. Should avoid php conflicts with class names and alias.
     *
     * @param string $class_name The class name.
     * @return ReflectionClass|Bool False if not a child of the Themosis\Model\BaseModel class.
     */
    private static function isChild($class_name)
    {
        $reflector = new ReflectionClass($class_name);

        if($reflector->isSubclassOf('Themosis\\Model\\BaseModel')){

            return $reflector;

        }

        return false;
    }

    /**
     * Performs a check to see if the class called belong to the
     * framework models.
     * 
     * @param string $name The class/file name.
     * @return bool True, False if not a model.
     */
    private static function isModel($name){

        return strrpos($name, static::$ext);

    }

    /**
     * Clean the class name and returns it.
     * 
     * @param string $name The class/file name.
     * @return string The clean/correct class name.
     */
    private static function clean($name){

        $name = substr($name, 0, static::isModel($name));

        return $name;

    }

    /**
     * Build the paths to load the models for the application.
     *
     * @return void
     */
    public static function add()
    {
        $path = themosis_path('datas').'models'.DS;
        static::append($path);
    }


    /**
     * Handle the model class aliases.
     * Developer can define an alias per class in order
     * to avoid conflicts at runtime.
     *
     * @return void
     */
    public static function alias()
    {
        foreach(static::$names as $name){

            $className = ucfirst($name);

            if(static::isModel($className)){

                $fullClassName = static::clean($className).static::$suffix;

                if(class_exists($fullClassName)){

                    if($reflector = static::isChild($fullClassName)){

                        /*---------------------------------------------------------*/
                        // Check if there is an alias defined.
                        /*---------------------------------------------------------*/
                        $properties = $reflector->getDefaultProperties();

                        if(isset($properties['alias']) && !empty($properties['alias'])){

                            if($fullClassName !== $properties['alias']){

                                class_alias($fullClassName, $properties['alias']);

                            }

                        } else {

                            $className = static::clean($className);

                            /*---------------------------------------------------------*/
                            // Use the $className from filename by default if no $alias
                            // is defined.
                            /*---------------------------------------------------------*/
                            class_alias($fullClassName, $className);

                        }
                    }
                }
            }
        }
    }

} 