<?php
    namespace Oak\Handlers;

    use \Oak\Runnable;

    /**
     * Base para el handler de pole, subpole y bronce
     *
     * @package Oak\Handlers
     * @author Almamu
     */
    abstract class BasePoleHandler implements Runnable
    {
        const TYPE_POLE = 0;
        const TYPE_SUBPOLE = 1;
        const TYPE_BRONCE = 2;

        /**
         * Mensaje a enviar por OAK cuando alguien obtiene un rango
         */
        const OBTAINED_MESSAGE = '%1$s ha obtenido %2$s';

        /**
         * @var array Puntos que se proporcionan por la pole
         */
        private $polePoints = array(
            self::TYPE_POLE => 3,
            self::TYPE_SUBPOLE => 2,
            self::TYPE_BRONCE => 1
        );

        /**
         * @var array Mensaje a usar cuando un usuario obtiene la pole
         */
        private $poleMessages = array(
            self::TYPE_POLE => 'la *pole*',
            self::TYPE_SUBPOLE => 'la *subpole*',
            self::TYPE_BRONCE => 'el *bronce*'
        );

        /**
         * Se encarga de gestionar la ejecucion del comando pole para las distintas formas disponibles
         * Realmente se podría dejar con el codigo anterior directamente en el "run" pero de esta
         * forma es algo más limpio el codigo y optimizado ya que no llamamos a todos esos text_has
         * cada vez
         *
         * @param $tipo string El tipo (pole/subpole/bronce)
         * @param telegram \Telegram Datos de la solicitud enviada por telegram al bot
         * @param $analytics \Analytics Analytics para el bot
         * @param $pokemon \Pokemon Modelo con datos para el bot
         */
        protected function handleFor($tipo, $telegram, $analytics, $pokemon)
        {
            $chat = $telegram->chat;
            $user = $telegram->user;

            $analytics->event("Telegram", "Pole");

            // es necesario comprobar si la pole se encuentra activada actualmente
            $pole = $pokemon->settings($chat->id, 'pole');

            if($pole != null && $pole == false)
            {
                return;
            }

            // Si está el Modo HARDCORE, la pole es cada hora. Si no, cada día.
            $timer = ($pokemon->settings($telegram->chat->id, 'pole_hardcore') ? "H" : "d");

            if(empty($pole) == false)
            {
                $pole = unserialize($pole);

                // es importante que la pole no se pueda repetir en el rango especificado por $timer
                // (hora o día)
                if(
                    ($tipo == self::TYPE_POLE && date($timer) == date($timer, $pole[self::TYPE_POLE])) ||
                    ($tipo == self::TYPE_SUBPOLE && date($timer) == date($timer, $pole[self::TYPE_SUBPOLE])) ||
                    ($tipo == self::TYPE_BRONCE && date($timer) == date($timer, $pole[self::TYPE_BRONCE]))
                )
                {
                    return;
                }
            }
            else
            {
                $pole = array(0, 0, 0);
            }

            $pole_info = unserialize($pokemon->settings($chat->id, 'pole_user'));
            $pkuser = $pokemon->user($user->id);
            $accion = "";

            // antes de decir que el usuario ha obtenido algo es necesario comprobar que en este momento no tiene ninguna
            // pole, subpole o bronce para evitar que alguien lo acapare
            if(
                (date($timer, $pole[self::TYPE_POLE]) == date($timer) && $pole_info[self::TYPE_POLE] == $user->id) ||
                (date($timer, $pole[self::TYPE_SUBPOLE]) == date($timer) && $pole_info[self::TYPE_SUBPOLE] == $user->id) ||
                (date($timer, $pole[self::TYPE_BRONCE]) == date($timer) && $pole_info[self::TYPE_BRONCE] == $user->id)
            )
            {
                // en la implementacion actual esto se podria hacer con un bucle, sin embargo al ser tres solo
                // creo que es mejor dejarlo como esta por temas de optimizacion
                // ademas no se descarta que TYPE_* cambie en un futuro si se añaden mas opciones
                return;
            }

            // se actualiza el array con los datos del ultimo ganador de la pole
            $pole[$tipo] = time();
            $pole_info[$tipo] = $user->id;

            // si la pole es diaria se incrementa el ranking
            if($pkuser && $timer == "d")
            {
                $pokemon->update_user_data($pkuser->telegramid, 'pole', $pkuser->pole + $this->polePoints[$tipo]);
            }

            $pokemon->settings($chat->id, 'pole', serialize($pole));
            $pokemon->settings($chat->id, 'pole_user', serialize($pole_user));

            $pkuser = $pokemon->user($telegram->user->id);

            if($telegram->text_has("pole", TRUE)){ // and date($timer) != date($timer, $pole[0])
                $pole = [time(), NULL, NULL];
                $pole_user = [$telegram->user->id, NULL, NULL];
                $action = "la *pole*";
                if($pkuser && $timer == "d"){ $pokemon->update_user_data($pkuser->telegramid, 'pole', ($pkuser->pole + 3)); }
            }elseif($telegram->text_has("subpole", TRUE) and date($timer) == date($timer, $pole[0]) and $pole_user[1] == NULL){
                if(in_array($telegram->user->id, $pole_user)){ return; } // Si ya ha hecho pole, nope.
                $pole[1] = time();
                $pole_user[1] = $telegram->user->id;
                $action = "la *subpole*";
                if($pkuser && $timer == "d"){ $pokemon->update_user_data($pkuser->telegramid, 'pole', ($pkuser->pole + 2)); }
            }elseif($telegram->text_has("bronce", TRUE) and date($timer) == date($timer, $pole[0]) and $pole_user[1] != NULL and $pole_user[2] == NULL){
                if(in_array($telegram->user->id, $pole_user)){ return; } // Si ya ha hecho sub/pole, nope.
                $pole[2] = time();
                $pole_user[2] = $telegram->user->id;
                $action = "el *bronce*";
                if($pkuser && $timer == "d"){ $pokemon->update_user_data($pkuser->telegramid, 'pole', ($pkuser->pole + 1)); }
            }else{
                return;
            }

            $pokemon->settings($chat->id, 'pole', serialize($pole));
            $pokemon->settings($chat->id, 'pole_user', serialize($pole_user));
            $telegram->send->text($telegram->user->first_name ." ha hecho $action!", TRUE)->send();
            // $telegram->send->text("Lo siento " .$telegram->user->first_name .", pero hoy la *pole* es mía! :D", TRUE)->send();
            return;
        }
    };