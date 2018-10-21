<?php

    namespace MoneyExchange;

    use pocketmine\plugin\PluginBase;
    use pocketmine\utils\Config;

    class main extends PluginBase
    {
        const SUCCESS_TAG = "§l§bSUCCESS §a>> §r";
        const ERROR_TAG = "§l§4ERROR §a>> §r";

        public $config;
        public $formId;

        public function onEnable(): void
        {
            $this->getLogger()->info("{$this->getDescription()->getName()} {$this->getDescription()->getVersion()} が読み込まれました");
            $this->getServer()->getPluginManager()->registerEvents(new eventListener($this), $this);
            $this->getServer()->getCommandMap()->register("exchange", new exchangeCommand($this));
            if (!file_exists($this->getDataFolder())) {
                mkdir($this->getDataFolder(), 0777);
            }
            $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
                "economy" => 100,
                "moneysystem" => 500,
            ));
            $this->formId[0] = rand(50000, 60000);
            $this->formId[1] = rand(50000, 60000);
            $this->formId[2] = rand(50000, 60000);
        }

        public function onDisable(): void
        {
            $this->getLogger()->info("{$this->getDescription()->getName()} {$this->getDescription()->getVersion()} が終了しました");
        }
    }