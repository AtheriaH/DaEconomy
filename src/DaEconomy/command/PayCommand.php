<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class PayCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("pay", "Pay money to another player", "/pay <player> <amount>");
        $this->setPermission("daeconomy.command.pay");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        // Bouncer 1: Must be an in-game player
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Only players can use this command.");
            return false;
        }

        // Bouncer 2: Did they actually type a name and an amount?
        if (count($args) < 2) {
            $sender->sendMessage(TF::RED . "Usage: /pay <player> <amount>");
            return false;
        }

        $targetName = $args[0];
        $amount = (int) $args[1];

        // Bouncer 3: No negative numbers or zero allowed! (Prevents stealing glitches)
        if ($amount <= 0) {
            $sender->sendMessage(TF::RED . "You must pay an amount greater than zero.");
            return false;
        }

        // Bouncer 4: Find the target player (Must be online so we can securely grab their XUID)
        $target = $this->plugin->getServer()->getPlayerExact($targetName);
        if ($target === null) {
            $sender->sendMessage(TF::RED . "Could not find player '$targetName'. Are they online?");
            return false;
        }

        // Bouncer 5: Stop players from paying themselves
        if ($target->getName() === $sender->getName()) {
            $sender->sendMessage(TF::RED . "You cannot pay yourself!");
            return false;
        }

        $senderXuid = $sender->getXuid();
        $targetXuid = $target->getXuid();
        $provider = $this->plugin->getProvider();

        // Bouncer 6: Does the sender actually have enough money?
        $senderBalance = $provider->getMoney($senderXuid);
        if ($senderBalance === false || $senderBalance < $amount) {
            $sender->sendMessage(TF::RED . "You do not have enough money. Your balance: $" . $senderBalance);
            return false;
        }

        // Make sure the target has an account in the database before receiving money
        if (!$provider->accountExists($targetXuid)) {
            $provider->createAccount($targetXuid);
        }

        // The actual transaction
        $provider->reduceMoney($senderXuid, $amount);
        $provider->addMoney($targetXuid, $amount);

        // Send receipts to both players
        $sender->sendMessage(TF::GREEN . "You successfully paid $" . number_format($amount) . " to " . $target->getName() . ".");
        $target->sendMessage(TF::GREEN . "You received $" . number_format($amount) . " from " . $sender->getName() . ".");

        return true;
    }
}
