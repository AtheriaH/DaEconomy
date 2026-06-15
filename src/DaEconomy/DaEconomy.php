<?php

declare(strict_types=1);

namespace DaEconomy;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class DaEconomy extends PluginBase{

    private Config $players;

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->players = new Config($this->getDataFolder() . "players.yml", Config::YAML);
        $this->getLogger()->info("DaEconomy enabled!");
    }

    public function getMoney(string $player): float{
        $default = (float) $this->getConfig()->get("starting-money", 1000.0);
        return (float) $this->players->get(strtolower($player), $default);
    }

    public function setMoney(string $player, float $amount): void{
        $this->players->set(strtolower($player), $amount);
        $this->players->save();
    }

    public function addMoney(string $player, float $amount): void{
        $this->setMoney($player, $this->getMoney($player) + $amount);
    }

    public function removeMoney(string $player, float $amount): bool{
        $current = $this->getMoney($player);
        if($current < $amount) return false;
        $this->setMoney($player, $current - $amount);
        return true;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        $symbol = (string) $this->getConfig()->get("currency-symbol", "$");

        switch(strtolower($command->getName())){
            
            case "money":
                if(!$sender instanceof Player){
                    $sender->sendMessage("§cThis command must be run in-game by a player.");
                    return true;
                }
                $sender->sendMessage("§aBalance: " . $symbol . $this->getMoney($sender->getName()));
                return true;

            case "addmoney":
                // Explicit Permission Check
                if(!$sender->hasPermission("daeconomy.command.addmoney")){
                    $sender->sendMessage("§cYou do not have permission to use this command.");
                    return true;
                }
                if(!isset($args[0], $args[1])) return false;
                
                // Math & Input Check
                if(!is_numeric($args[1]) || (float)$args[1] <= 0){
                    $sender->sendMessage("§cPlease provide a valid positive amount.");
                    return true;
                }
                
                $amount = (float)$args[1];
                $this->addMoney($args[0], $amount);
                $sender->sendMessage("§aAdded " . $symbol . $amount . " to " . $args[0]);
                return true;

            case "removemoney":
                // Explicit Permission Check
                if(!$sender->hasPermission("daeconomy.command.removemoney")){
                    $sender->sendMessage("§cYou do not have permission to use this command.");
                    return true;
                }
                if(!isset($args[0], $args[1])) return false;
                
                // Math & Input Check
                if(!is_numeric($args[1]) || (float)$args[1] <= 0){
                    $sender->sendMessage("§cPlease provide a valid positive amount.");
                    return true;
                }
                
                $amount = (float)$args[1];
                if(!$this->removeMoney($args[0], $amount)){
                    $sender->sendMessage("§cPlayer does not have enough money.");
                    return true;
                }
                $sender->sendMessage("§aRemoved " . $symbol . $amount . " from " . $args[0]);
                return true;
        }
        return false;
    }
}

