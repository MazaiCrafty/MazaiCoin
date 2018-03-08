<?php

/*
 * ___  ___               _ _____            __ _         
 * |  \/  |              (_)  __ \          / _| |        
 * | .  . | __ _ ______ _ _| /  \/_ __ __ _| |_| |_ _   _ 
 * | |\/| |/ _` |_  / _` | | |   | '__/ _` |  _| __| | | |
 * | |  | | (_| |/ / (_| | | \__/\ | | (_| | | | |_| |_| |
 * \_|  |_/\__,_/___\__,_|_|\____/_|  \__,_|_|  \__|\__, |
 *                                                   __/ |
 *                                                  |___/
 * Copyright (C) 2017-2018 @MazaiCrafty (https://twitter.com/MazaiCrafty)
 *
 * This program is free plugin.
 */

namespace jp\mazaicrafty\pmmp\MazaiCoin;

# Base
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;

# Events
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

# Commands
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecuter;
use pocketmine\command\ConsoleCommandSender;

# Utils
use pocketmine\utils\TextFormat as COLOR;
use pocketmine\utils\Config;

class main extends PluginBase implements Listener{

    public function onEnable(): void{
        date_default_timezone_set('Asia/Tokyo');
        $this->allRegisterEvents();

        $this->manager = new Config($this->getDataFolder() . "CoinManager.yml", Config::YAML);
        $this->timedate = new Config($this->getDataFolder() . "Date.yml", Config::YAML);
        $this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, array(
            "VOTECOIN" => "1",
            "VOTEMESSAGE" => "投票が完了しました。魔剤コインが一枚増えました。"
        ));
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        switch ($command->getName()){
            case "vote":
            $name = $sender->getName();
            $time = date("Y-m-d H:i:s", strtotime("+1 day"));
            if (!$this->manager->exists($name)){
                $this->manager->set($name, 0);
                $this->manager->save();
            }else{
                $date = $this->timedate->get($name);
                $now = date("Y-m-d H:i:s");
                if ($date > $now){
                    $sender->sendMessage("既に投票しています\n".
                "一日経ってから再度実行してください");
                }
                else{
                    $coin = $this->manager->get($name);
                    $plus = $this->config->get("VOTECOIN");
                    $vote_mes = $this->config->get("VOTEMESSAGE");
                    $this->manager->set($name, $coin + $plus);
                    $this->manager->save();
                    $this->timedate->set($name, $time);
                    $this->timedate->save();
                    $sender->sendMessage($vote_mes);
                }
            }
        }
        return true;
    }

    public function onJoin(PlayerJoinEvent $event){
        $name = $event->getPlayer()->getName();
        if (!$this->manager->exists($name)){
            $this->manager->set($name, 0);
            $this->manager->save();
        }
    }
/*
    public function onVote(Player $player){
        $name = $player->getName();
        $time = date("Y-m-d H:i:s", strtotime("-1 day"));
        $this->timedate->set($name, $time);
        $this->timedate->save();
        if (!$this->manager->exists($name)){
            $this->manager->set($name, 0);
            $this->manager->save();
        }
        else{
            $date = $this->timedate->get($name);
            $now = date("Y-m-d H:i:s");
            if (!$time < $now){
                $player->sendMessage("既に投票しています\n".
            "一日経ってから再度実行してください");
            }
            else{
                $coin = $this->manager->get($name);
                $plus = $this->config->get("VOTECOIN");
                $vote_mes = $this->config->get("VOTEMESSAGE");
                $this->manager->set($name, $coin + $plus);
                $this->manager->save();
                $player->sendMessage($vote_mes);
            }
        }
    }
*/
    public function getCoin($player){
        $name = $player;
        if (!$this->manager->exists($name)){
            $this->manager->set($name, 0);
            $this->manager->save();
            return 0;
        }
        else{
            $coin = $this->manager->get($name);
            return $coin;
        }
    }

    public function setCoin($player, $amount){
        $name = $player;
        $coin = $amount;
        if (!$this->manager->exists($name)){
            $this->manager->set($name, $coin);
            $this->manager->save();
        }
        else{
            $this->manager->set($name, $coin);
            $this->manager->save();
        }
    }

    public function takeCoin($player, $amount){
        $name = $player;
        if (!$this->manager->exists($name)){
            $this->manager->set($name, 0);
            $this->manager->save();
        }
        else{
            $coin = $this->manager->get($name);
            $_coin = $coin - $amount;
            $this->manager->set($name, $_coin);
            $this->manager->save();
        }
    }

    public function allRegisterEvents(){
        if(!file_exists($this->getDataFolder())){
            mkdir($this->getDataFolder(), 0755, true); 
            }
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
    }
}
