<?php
    namespace Oak\CommandHandler;

    use \Exception;

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Handler basico para los comandos de oak que permite mostrar datos de debug sobre el mismo
     *
     * Aqui es posible añadir el codigo de debug que se desee, pero seria recomendable dejarla plantilla intacta
     * en los commits al servicio para evitar cambios innecesarios sobre git
     *
     * @package Oak\CommandHandler
     * @author Almamu
     */
    class DebugHandler implements Handler
    {
        /**
         * @var array Mapa con todos los datos de los comandos disponibles
         */
        private $classMap = array();

        /**
         * @inheritdoc
         */
        public function registerHandler($command, $class, $description = "")
        {
            // comprobar si el comando ya esta registrado
            if(array_key_exists($command, $this->classMap) === true)
            {
                throw new Exception("El comando " . $command . " ya se encuentra registrado, sobreescribir comandos no soportado");
            }

            if(in_array("Oak\\Runnable", class_implements($class, true)) == false)
            {
                throw new Exception("La clase " . $class . " ha de implementar \\Oak\\Runnable");
            }

            // añadir el comando a la lista
            $this->classMap[$command] = $class;
        }

        /**
         * @inheritdoc
         */
        public function isCommand($telegram)
        {
            return $telegram->text_contains("/", true);
        }

        /**
         * @inheritdoc
         */
        public function run($telegram, $analytics, $pokemon)
        {
            // es necesario eliminar la / del principio
            $command = substr($telegram->words(0, 1), 1);

            // comprobar si el comando existe obviamente
            if(array_key_exists($command, $this->classMap) === false)
            {
                throw new Exception("El comando " . $command . " no existe");
            }

            // obtenemos el nombre de la clase de la lista de comandos
            $class = $this->classMap[$command];

            // instanciamos la clase en cuestion y ejecutamos el metodo "run" que todas tienen
            $obj = new $class();
            $obj->run($telegram, $analytics, $pokemon);
        }
    };