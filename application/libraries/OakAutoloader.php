<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Autoloader que se encarga de cargar lo necesario para la gestion y ejecucion de comandos
     *
     * @author Almamu
     */
    class CommandHandlerAutoloader
    {
        /**
         * @var int Longitud del namespace para la libreria de comandos
         */
        private $namespaceNameLength = 4;

        /**
         * @var string Nombre del namespace para la libreria de comandos
         */
        private $namespaceName = "Oak\\";

        /**
         * CommandHandlerAutoloader constructor.
         * Define el autoloader para la libreria en el sistema de PHP
         */
        public function __construct()
        {
            spl_autoload_register(array($this, 'loader'));
        }

        /**
         * Hace de autoloader para las clases del command handler
         *
         * @param $className string El nombre de la clase para la que hacer el autoload
         */
        public function loader($className)
        {
            // se asegura de que el espacio de nombres es el correcto
            // tambien se podria hacer un array de clases pero esto es mas problematico
            // de mantener
            if(strncmp($className, $this->namespaceName, $this->namespaceNameLength) === 0)
            {
                require APPPATH . 'classes' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            }
        }
    };