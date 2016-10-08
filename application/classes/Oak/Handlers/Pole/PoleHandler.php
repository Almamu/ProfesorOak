<?php
    namespace Oak\Handlers\Pole;

    use Oak\Handlers\BasePoleHandler;

    class PoleHandler extends BasePoleHandler
    {
        /**
         * @inheritdoc
         */
        function run($telegram, $analytics, $pokemon)
        {
            $this->handleFor(self::TYPE_POLE, $telegram, $analytics, $pokemon);
        }
    };