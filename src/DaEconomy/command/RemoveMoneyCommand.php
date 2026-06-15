<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class RemoveMoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("removemoney", "Deduct money from a player balance", "/removemoney <player> <amount>", []);
        $this->setPermission("daeconomy.command.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return false;
        }

        $amount = (int) $args[1];
        
        if ($amount <= 0) {
            $sender->sendMessage(TextFormat::RED . "Please enter a valid amount greater than zero.");
            return false;
        }

        $target = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
        $realName = $target?->getName() ?? $args[0];

        $this->plugin->removeBalance($realName, $amount);
        $sender->sendMessage(TextFormat::GREEN . "Successfully removed " . $this->plugin->formatMoney($amount) . " from " . $realName);
        
        $target?->sendMessage(TextFormat::RED . $this->plugin->formatMoney($amount) . " was deducted from your balance.");
        return true;
    }
}
