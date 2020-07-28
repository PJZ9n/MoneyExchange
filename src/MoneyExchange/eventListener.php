<?php

    namespace MoneyExchange;

    use metowa1227\moneysystem\api\core\API as MoneySystemAPI;
    use onebone\economyapi\EconomyAPI;
    use pocketmine\event\Listener;
    use pocketmine\event\server\DataPacketReceiveEvent;
    use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
    use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
    use pocketmine\plugin\Plugin;

    class eventListener implements Listener
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onDataPacketReceive(DataPacketReceiveEvent $event)
        {
            $player = $event->getPlayer();
            //$name = $player->getName();
            $receive_packet = $event->getPacket();
            if ($receive_packet instanceof ModalFormResponsePacket) {
                $response = json_decode($receive_packet->formData, true);
                $formId = $receive_packet->formId;
                if ($formId === $this->plugin->formId[0]) {
                    if ($response !== null) {
                        $economy_money = EconomyAPI::getInstance()->myMoney($player);
                        $economy_unit = EconomyAPI::getInstance()->getMonetaryUnit();
                        $moneysystem_money = MoneySystemAPI::getInstance()->get($player);
                        $moneysystem_unit = MoneySystemAPI::getInstance()->getUnit();
                        switch ($response) {
                            case 0:
                                $steps = array();
                                for ($i = 0; $i < 50; $i++) {
                                    $steps[] = $economy_unit . ($i + 1) * $this->plugin->config->get("economy") . " => " . $moneysystem_unit . ($i + 1) * $this->plugin->config->get("moneysystem");
                                }
                                $packet = new ModalFormRequestPacket();
                                $packet->formData = json_encode(array(
                                    "type" => "custom_form",
                                    "title" => "MoneyExchange >> {$economy_unit}を{$moneysystem_unit}へ",
                                    "content" => array(
                                        array(
                                            "type" => "label",
                                            "text" => "所持金({$economy_unit}): {$economy_unit}{$economy_money}\n所持金({$moneysystem_unit}): {$moneysystem_unit}{$moneysystem_money}\n\nどれ位交換しますか？",
                                        ),
                                        array(
                                            "type" => "step_slider",
                                            "text" => "交換する量",
                                            "steps" => $steps,
                                        ),
                                    ),
                                ));
                                $packet->formId = $this->plugin->formId[1];
                                $player->sendDataPacket($packet);
                                break;
                            case 1:
                                $steps = array();
                                for ($i = 0; $i < 50; $i++) {
                                    $steps[] = $moneysystem_unit . ($i + 1) * $this->plugin->config->get("moneysystem") . " => " . $economy_unit . ($i + 1) * $this->plugin->config->get("economy");
                                }
                                $packet = new ModalFormRequestPacket();
                                $packet->formData = json_encode(array(
                                    "type" => "custom_form",
                                    "title" => "MoneyExchange >> {$moneysystem_unit}を{$economy_unit}へ",
                                    "content" => array(
                                        array(
                                            "type" => "label",
                                            "text" => "所持金({$economy_unit}): {$economy_unit}{$economy_money}\n所持金({$moneysystem_unit}): {$moneysystem_unit}{$moneysystem_money}\n\nどれ位交換しますか？",
                                        ),
                                        array(
                                            "type" => "step_slider",
                                            "text" => "交換する量",
                                            "steps" => $steps,
                                        ),
                                    ),
                                ));
                                $packet->formId = $this->plugin->formId[2];
                                $player->sendDataPacket($packet);
                                break;
                        }
                    }
                } else if ($formId === $this->plugin->formId[1]) {
                    if ($response !== null) {
                        $economy_money = EconomyAPI::getInstance()->myMoney($player);
                        $economy_unit = EconomyAPI::getInstance()->getMonetaryUnit();
                        //$moneysystem_money = MoneySystemAPI::getInstance()->check($player);
                        $moneysystem_unit = MoneySystemAPI::getInstance()->getUnit();
                        $moneysystem_rate = array();
                        $economy_rate = array();
                        for ($i = 0; $i < 50; $i++) {
                            $moneysystem_rate[] = ($i + 1) * $this->plugin->config->get("moneysystem");
                        }
                        for ($i = 0; $i < 50; $i++) {
                            $economy_rate[] = ($i + 1) * $this->plugin->config->get("economy");
                        }
                        //$player->sendMessage($economy_unit . $economy_rate[$response[1]] . " => " . $moneysystem_unit . $moneysystem_rate[$response[1]]);
                        if ($economy_money >= $economy_rate[$response[1]]) {
                            EconomyAPI::getInstance()->reduceMoney($player, $economy_rate[$response[1]]);
                            MoneySystemAPI::getInstance()->increase($player, $moneysystem_rate[$response[1]]);
                            $player->sendMessage(main::SUCCESS_TAG . "{$economy_unit}{$economy_rate[$response[1]]}を{$moneysystem_unit}{$moneysystem_rate[$response[1]]}に交換しました");
                        } else {
                            $player->sendMessage(main::ERROR_TAG . "所持金({$economy_unit})が足りません");
                        }
                    }
                } else if ($formId === $this->plugin->formId[2]) {
                    if ($response !== null) {
                        //$economy_money = EconomyAPI::getInstance()->myMoney($player);
                        $economy_unit = EconomyAPI::getInstance()->getMonetaryUnit();
                        $moneysystem_money = MoneySystemAPI::getInstance()->get($player);
                        $moneysystem_unit = MoneySystemAPI::getInstance()->getUnit();
                        $moneysystem_rate = array();
                        $economy_rate = array();
                        for ($i = 0; $i < 50; $i++) {
                            $moneysystem_rate[] = ($i + 1) * $this->plugin->config->get("moneysystem");
                        }
                        for ($i = 0; $i < 50; $i++) {
                            $economy_rate[] = ($i + 1) * $this->plugin->config->get("economy");
                        }
                        //$player->sendMessage($moneysystem_unit . $moneysystem_rate[$response[1]] . " => " . $economy_unit . $economy_rate[$response[1]]);
                        if ($moneysystem_money >= $moneysystem_rate[$response[1]]) {
                            MoneySystemAPI::getInstance()->reduce($player, $moneysystem_rate[$response[1]]);
                            EconomyAPI::getInstance()->addMoney($player, $economy_rate[$response[1]]);
                            $player->sendMessage(main::SUCCESS_TAG . "{$moneysystem_unit}{$moneysystem_rate[$response[1]]}を{$economy_unit}{$economy_rate[$response[1]]}に交換しました");
                        } else {
                            $player->sendMessage(main::ERROR_TAG . "所持金({$moneysystem_unit})が足りません");
                        }
                    }
                }
            }
        }
    }