<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("money", "Check account balances", "/money [player]", []);
        $this->setPermission("daeconomy.command.money");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        $targetName = $args[0] ?? null;

        if ($targetName === null) {
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "Please specify a player name from the console.");
                return false;
            }
            $balance = $this->plugin->getBalance($sender->getName());
            $sender->sendMessage(TextFormat::GREEN . "Your Balance: " . TextFormat::YELLOW . $this->plugin->formatMoney($balance));
            return true;
        }

        $target = $this->plugin->getServer()->getPlayerByPrefix($targetName);
        $realName = $target !== null ? $target->getName() : $targetName;
        
        $balance = $this->plugin->getBalance($realName);
        $sender->sendMessage(TextFormat::GREEN . $realName . "'s Balance: " . TextFormat::YELLOW . $this->plugin->formatMoney($balance));
        return true;
    }
}
