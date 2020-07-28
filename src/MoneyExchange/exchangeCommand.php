<?php

    namespace MoneyExchange;


    use metowa1227\moneysystem\api\core\API as MoneySystemAPI;
    use onebone\economyapi\EconomyAPI;
    use pocketmine\command\Command;
    use pocketmine\command\CommandSender;
    use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
    use pocketmine\Player;
    use pocketmine\plugin\Plugin;

    class exchangeCommand extends Command
    {

        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
            parent::__construct("exchange", "MoneyExchangeのメインコマンド", "/exchange");
            $this->setPermission("moneyexchange.command.exchange");
        }

        public function execute(CommandSender $sender, string $commandLabel, array $args)
        {
            if (!$sender instanceof Player) {
                $sender->sendMessage(main::ERROR_TAG . "このコマンドはプレイヤーのみ実行できます。");
                return true;
            }
            //$name = $sender->getName();
            $economy_money = EconomyAPI::getInstance()->myMoney($sender);
            $economy_unit = EconomyAPI::getInstance()->getMonetaryUnit();
            $moneysystem_money = MoneySystemAPI::getInstance()->get($sender);
            $moneysystem_unit = MoneySystemAPI::getInstance()->getUnit();
            $packet = new ModalFormRequestPacket();
            $packet->formData = json_encode(array(
                "type" => "form",
                "title" => "MoneyExchange",
                "content" => "所持金({$economy_unit}): {$economy_unit}{$economy_money}\n所持金({$moneysystem_unit}): {$moneysystem_unit}{$moneysystem_money}",
                "buttons" => array(
                    array(
                        "text" => "{$economy_unit}を{$moneysystem_unit}へ",
                    ),
                    array(
                        "text" => "{$moneysystem_unit}を{$economy_unit}へ",
                    ),
                ),
            ));
            $packet->formId = $this->plugin->formId[0];
            $sender->sendDataPacket($packet);
            return true;
        }

    }